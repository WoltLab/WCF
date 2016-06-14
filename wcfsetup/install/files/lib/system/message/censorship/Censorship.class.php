<?php
namespace wcf\system\message\censorship;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Finds censored words.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Censorship
 */
class Censorship extends SingletonFactory {
	/**
	 * censored words
	 * @var	string[]
	 */
	protected $censoredWords = [];
	
	/**
	 * word delimiters
	 * @var	string
	 */
	protected $delimiters = '[\s\x21-\x2F\x3A-\x3F\x5B-\x60\x7B-\x7E]';
	
	/**
	 * list of words
	 * @var	string[]
	 */
	protected $words = [];
	
	/**
	 * list of matches
	 * @var	array
	 */
	protected $matches = [];
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		// get words which should be censored
		$censoredWords = explode("\n", StringUtil::unifyNewlines(mb_strtolower(CENSORED_WORDS)));
		
		// format censored words
		for ($i = 0, $length = count($censoredWords); $i < $length; $i++) {
			$censoredWord = StringUtil::trim($censoredWords[$i]);
			if (empty($censoredWord)) {
				continue;
			}
			
			$displayedCensoredWord = str_replace(['~', '*'], '', $censoredWord);
			
			// check if censored word contains at least one delimiter
			if (preg_match('!'.$this->delimiters.'+!', $displayedCensoredWord)) {
				// remove delimiters
				$censoredWord = preg_replace('!'.$this->delimiters.'!', '', $censoredWord);
				
				// enforce partial matching
				$censoredWord = '~' . $censoredWord;
			}
			
			$this->censoredWords[$displayedCensoredWord] = $censoredWord;
		}
	}
	
	/**
	 * Returns censored words from a text.
	 * 
	 * @param	string		$text
	 * @return	mixed		$matches / false
	 */
	public function test($text) {
		// reset values
		$this->matches = $this->words = [];
		
		// string to lower case
		$text = mb_strtolower($text);
		
		// ignore bbcode tags
		$text = preg_replace('~\[/?[a-z]+[^\]]*\]~i', '', $text);
		
		// split the text in single words
		$this->words = preg_split("!".$this->delimiters."+!", $text, -1, PREG_SPLIT_NO_EMPTY);
		
		// check each word if it's censored.
		for ($i = 0, $count = count($this->words); $i < $count; $i++) {
			$word = $this->words[$i];
			foreach ($this->censoredWords as $displayedCensoredWord => $censoredWord) {
				// check for direct matches ("badword" == "badword")
				if ($censoredWord == $word) {
					// store censored word
					if (isset($this->matches[$word])) {
						$this->matches[$word]++;
					}
					else {
						$this->matches[$word] = 1;
					}
						
					continue 2;
				}
				// check for asterisk matches ("*badword*" == "FooBadwordBar")
				else if (mb_strpos($censoredWord, '*') !== false) {
					$censoredWord = str_replace('\*', '.*', preg_quote($censoredWord));
					if (preg_match('!^'.$censoredWord.'$!', $word)) {
						// store censored word
						if (isset($this->matches[$word])) {
							$this->matches[$word]++;
						}
						else {
							$this->matches[$word] = 1;
						}
						
						continue 2;
					}
				}
				// check for partial matches ("~badword~" == "bad-word")
				else if (mb_strpos($censoredWord, '~') !== false) {
					$censoredWord = str_replace('~', '', $censoredWord);
					if (($position = mb_strpos($censoredWord, $word)) !== false) {
						if ($position > 0) {
							// look behind
							if (!$this->lookBehind($i - 1, mb_substr($censoredWord, 0, $position))) {
								continue;
							}
						}
						
						if ($position + mb_strlen($word) < mb_strlen($censoredWord)) {
							// look ahead
							if (($newIndex = $this->lookAhead($i + 1, mb_substr($censoredWord, $position + mb_strlen($word))))) {
								$i = $newIndex;
							}
							else {
								continue;
							}
						}
						
						// store censored word
						if (isset($this->matches[$displayedCensoredWord])) {
							$this->matches[$displayedCensoredWord]++;
						}
						else {
							$this->matches[$displayedCensoredWord] = 1;
						}
						
						continue 2;
					}
				}
			}
		}
		
		// at least one censored word was found
		if (count($this->matches) > 0) {
			return $this->matches;
		}
		// text is clean
		else {
			return false;
		}
	}
	
	/**
	 * Looks behind in the word list.
	 * 
	 * @param	integer		$index
	 * @param	string		$search
	 * @return	boolean
	 */
	protected function lookBehind($index, $search) {
		if (isset($this->words[$index])) {
			if (mb_strpos($this->words[$index], $search) === (mb_strlen($this->words[$index]) - mb_strlen($search))) {
				return true;
			}
			else if (mb_strpos($search, $this->words[$index]) === (mb_strlen($search) - mb_strlen($this->words[$index]))) {
				return $this->lookBehind($index - 1, mb_substr($search, 0, (mb_strlen($search) - mb_strlen($this->words[$index]))));
			}
		}
		
		return false;
	}
	
	/**
	 * Looks ahead in the word list.
	 * 
	 * @param	integer		$index
	 * @param	string		$search
	 * @return	mixed
	 */
	protected function lookAhead($index, $search) {
		if (isset($this->words[$index])) {
			if (mb_strpos($this->words[$index], $search) === 0) {
				return $index;
			}
			else if (mb_strpos($search, $this->words[$index]) === 0) {
				return $this->lookAhead($index + 1, mb_substr($search, mb_strlen($this->words[$index])));
			}
		}
		
		return false;
	}
}
