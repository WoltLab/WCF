<?php
namespace wcf\system\search;
use wcf\system\bbcode\KeywordHighlighter;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Formats messages for search result output.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
class SearchResultTextParser extends SingletonFactory {
	/**
	 * max length for message abstract
	 * @var	integer
	 */
	const MAX_LENGTH = 500;
	
	/**
	 * highlight query
	 * @var	mixed
	 */
	protected $searchQuery = '';
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		if (isset($_GET['highlight'])) {
			$keywordString = $_GET['highlight'];
			
			// remove search operators
			$keywordString = preg_replace('/[\+\-><()~\*]+/', '', $keywordString);
			
			if (mb_substr($keywordString, 0, 1) == '"' && mb_substr($keywordString, -1) == '"') {
				// phrases search
				$keywordString = StringUtil::trim(mb_substr($keywordString, 1, -1));
				
				if (!empty($keywordString)) {
					$this->searchQuery = $keywordString;
				}
			}
			else {
				$this->searchQuery = ArrayUtil::trim(explode(' ', $keywordString));
				if (empty($this->searchQuery)) {
					$this->searchQuery = false;
				}
				else if (count($this->searchQuery) == 1) {
					$this->searchQuery = reset($this->searchQuery);
				}
			}
		}
	}
	
	/**
	 * Returns an abstract of the given message.
	 * Uses search keywords to shift the start and end position of the abstract (like Google).
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	protected function getMessageAbstract($text) {
		// replace newlines with spaces
		$text = Regex::compile("\s+", Regex::UTF_8)->replace($text, ' ');
		
		if (mb_strlen($text) > static::MAX_LENGTH) {
			if ($this->searchQuery) {
				// phrase search
				if (!is_array($this->searchQuery)) {
					$start = mb_strripos($text, $this->searchQuery);
					if ($start !== false) {
						$end = $start + mb_strlen($this->searchQuery);
						$shiftStartBy = $shiftEndBy = round((static::MAX_LENGTH - mb_strlen($this->searchQuery)) / 2);
						
						// shiftStartBy is negative when search query length is over max length
						if ($shiftStartBy < 0) {
							$shiftEndBy += $shiftStartBy;
							$shiftStartBy = 0;
						}
						
						// shift abstract start
						if ($start - $shiftStartBy < 0) {
							$shiftEndBy += $shiftStartBy - $start;
							$start = 0;
						}
						else {
							$start -= $shiftStartBy;
						}
						
						// shift abstract end
						if ($end + $shiftEndBy > mb_strlen($text) - 1) {
							$shiftStartBy = $end + $shiftEndBy - mb_strlen($text) - 1;
							if ($shiftStartBy > $start) {
								$start = 0;
							}
							else {
								$start -= $shiftStartBy;
							}
						}
						else {
							$end += $shiftEndBy;
						}
						
						$newText = '';
						if ($start > 0) $newText .= StringUtil::HELLIP;
						$newText .= mb_substr($text, $start, $end - $start);
						if ($end < mb_strlen($text) - 1) $newText .= StringUtil::HELLIP;
						return $newText;
					}
				}
				else {
					$matches = [];
					$shiftLength = static::MAX_LENGTH;
					// find first match of each keyword
					foreach ($this->searchQuery as $keyword) {
						$start = mb_strripos($text, $keyword);
						if ($start !== false) {
							$shiftLength -= mb_strlen($keyword);
							$matches[$keyword] = ['start' => $start, 'end' => $start + mb_strlen($keyword)];
						}
					}
					
					// shift match position
					$shiftBy = round(($shiftLength / count($this->searchQuery)) / 2);
					foreach ($matches as $keyword => $position) {
						$position['start'] -= $shiftBy;
						$position['end'] += $shiftBy;
						$matches[$keyword] = $position;
					}
					
					$start = 0;
					$end = mb_strlen($text) - 1;
					$newText = '';
					$i = 0;
					$length = count($matches);
					foreach ($matches as $keyword => $position) {
						if ($position['start'] < $start) {
							$position['end'] += $start - $position['start'];
							$position['start'] = $start;
						}
						
						if ($position['end'] > $end) {
							if ($position['start'] > $start) {
								$shiftStartBy = $position['end'] - $end;
								if ($position['start'] - $shiftStartBy < $start) {
									$shiftStartBy = $position['start'] - $start;
								}
								
								$position['start'] -= $shiftStartBy;
							}
							
							$position['end'] = $end;
						}
						
						if ($position['start'] > $start) $newText .= StringUtil::HELLIP;
						$newText .= mb_substr($text, $position['start'], $position['end'] - $position['start']);
						if ($i == $length - 1 && $position['end'] < $end) $newText .= StringUtil::HELLIP;
						
						$start = $position['end'];
						$i++;
					}
					
					if (!empty($newText)) return $newText;
				}
			}
			
			// no search query or no matches
			return mb_substr($text, 0, static::MAX_LENGTH) . StringUtil::HELLIP;
		}
		
		return $text;
	}
	
	/**
	 * Formats a message for search result output.
	 * 
	 * @param	string		$text
	 * @return	string
	 */
	public function parse($text) {
		// remove nonessentials
		$text = Regex::compile('<!-- begin:parser_nonessential -->.*?<!-- end:parser_nonessential -->', Regex::DOT_ALL)->replace($text, '');
		
		// remove html codes
		$text = StringUtil::stripHTML($text);
		
		// decode html
		$text = StringUtil::decodeHTML($text);
		
		// get abstract
		$text = $this->getMessageAbstract($text);
		
		// encode html
		$text = StringUtil::encodeHTML($text);
		
		// do highlighting
		return KeywordHighlighter::getInstance()->doHighlight($text);
	}
}
