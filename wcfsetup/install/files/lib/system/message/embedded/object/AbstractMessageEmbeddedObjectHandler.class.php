<?php
namespace wcf\system\message\embedded\object;
use wcf\data\object\type\ObjectType;
use wcf\data\DatabaseObjectDecorator;
use wcf\util\ArrayUtil;

/**
 * Provides default implementations for message embedded object handlers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.embedded.object
 * @category	Community Framework
 * 
 * @method	ObjectType	getDecoratedObject()
 * @mixin	ObjectType
 */
abstract class AbstractMessageEmbeddedObjectHandler extends DatabaseObjectDecorator implements IMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ObjectType::class;
	
	/**
	 * Parses given message for specific bbcode parameters.
	 * 
	 * @param	string		$message
	 * @param	string		$bbcode		bbcode name
	 * @return	array
	 */
	public static function getTextParameters($message, $bbcode) {
		if (preg_match_all('~\['.$bbcode.'\](.*?)\[/'.$bbcode.'\]~i', $message, $matches)) {
			$results = ArrayUtil::trim($matches[1]);
			$results = array_unique($results);
			
			return $results;
		}
		
		return [];
	}
	
	/**
	 * Parses given message for specific bbcode parameters.
	 * 
	 * @param	string		$message
	 * @param	string		$bbcode		bbcode name
	 * @return	array
	 */
	public static function getFirstParameters($message, $bbcode) {
		$pattern = '~\['.$bbcode.'=
				(\'(?:[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'|(?:[^,\]]*))
				(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
			\]~ix';
		
		if (preg_match_all($pattern, $message, $matches)) {
			foreach ($matches[1] as &$value) {
				// remove quotes
				if (mb_substr($value, 0, 1) == "'" && mb_substr($value, -1) == "'") {
					$value = str_replace("\'", "'", $value);
					$value = str_replace("\\\\", "\\", $value);
				
					$value = mb_substr($value, 1, -1);
				}
			}
			
			$results = ArrayUtil::trim($matches[1]);
			$results = array_unique($results);
			
			return $results;
		}
		
		return [];
	}
}
