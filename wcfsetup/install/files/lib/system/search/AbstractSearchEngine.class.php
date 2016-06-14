<?php
namespace wcf\system\search;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\util\StringUtil;

/**
 * Default implementation for search engines, this class should be extended by
 * all search engines to preserve compatibility in case of interface changes.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Search
 */
abstract class AbstractSearchEngine extends SingletonFactory implements ISearchEngine {
	/**
	 * class name for preferred condition builder
	 * @var	string
	 */
	protected $conditionBuilderClassName = PreparedStatementConditionBuilder::class;
	
	/**
	 * list of engine-specific special characters
	 * @var	string[]
	 */
	protected $specialCharacters = [];
	
	/**
	 * @inheritDoc
	 */
	public function getConditionBuilderClassName() {
		return $this->conditionBuilderClassName;
	}
	
	/**
	 * Manipulates the search term (< and > used as quotation marks):
	 * 
	 * - <test foo> becomes <+test* +foo*>
	 * - <test -foo bar> becomes <+test* -foo* +bar*>
	 * - <test "foo bar"> becomes <+test* +"foo bar">
	 * 
	 * @see	http://dev.mysql.com/doc/refman/5.5/en/fulltext-boolean.html
	 * 
	 * @param	string		$query
	 * @return	string
	 */
	protected function parseSearchQuery($query) {
		$query = StringUtil::trim($query);
		
		// expand search terms with a * unless they're encapsulated with quotes
		$inQuotes = false;
		$previousChar = $tmp = '';
		$controlCharacterOrSpace = false;
		$chars = ['+', '-', '*'];
		$ftMinWordLen = $this->getFulltextMinimumWordLength();
		for ($i = 0, $length = mb_strlen($query); $i < $length; $i++) {
			$char = mb_substr($query, $i, 1);
			
			if ($inQuotes) {
				if ($char == '"') {
					$inQuotes = false;
				}
			}
			else {
				if ($char == '"') {
					$inQuotes = true;
				}
				else {
					if ($char == ' ' && !$controlCharacterOrSpace) {
						$controlCharacterOrSpace = true;
						$tmp .= '*';
					}
					else if (in_array($char, $chars)) {
						$controlCharacterOrSpace = true;
					}
					else {
						$controlCharacterOrSpace = false;
					}
				}
			}
			
			/*
			 * prepend a plus sign (logical AND) if ALL these conditions are given:
			 * 
			 * 1) previous character:
			 *   - is empty (start of string)
			 *   - is a space (MySQL uses spaces to separate words)
			 * 
			 * 2) not within quotation marks
			 * 
			 * 3) current char:
			 *   - is NOT +, - or *
			 */
			if (($previousChar == '' || $previousChar == ' ') && !$inQuotes && !in_array($char, $chars)) {
				// check if the term is shorter than the minimum fulltext word length
				if ($i + $ftMinWordLen <= $length) {
					$term = '';// $char;
					for ($j = $i, $innerLength = $ftMinWordLen + $i; $j < $innerLength; $j++) {
						$currentChar = mb_substr($query, $j, 1);
						if ($currentChar == '"' || $currentChar == ' ' || in_array($currentChar, $chars)) {
							break;
						}
						
						$term .= $currentChar;
					}
					
					if (mb_strlen($term) == $ftMinWordLen) {
						$tmp .= '+';
					}
				}
			}
			
			$tmp .= $char;
			$previousChar = $char;
		}
		
		// handle last char
		if (!$inQuotes && !$controlCharacterOrSpace) {
			$tmp .= '*';
		}
		
		return $tmp;
	}
	
	/**
	 * Returns minimum word length for fulltext indices.
	 * 
	 * @return	integer
	 */
	abstract protected function getFulltextMinimumWordLength();
	
	/**
	 * @inheritDoc
	 */
	public function removeSpecialCharacters($string) {
		if (!empty($this->specialCharacters)) {
			return str_replace($this->specialCharacters, '', $string);
		}
		
		return $string;
	}
}
