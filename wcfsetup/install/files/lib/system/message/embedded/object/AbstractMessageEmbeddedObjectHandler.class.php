<?php
namespace wcf\system\message\embedded\object;
use wcf\data\DatabaseObjectDecorator;
use wcf\util\ArrayUtil;

/**
 * Provides default implementations for message embedded object handlers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.message.embedded.object
 * @category	Community Framework
 */
abstract class AbstractMessageEmbeddedObjectHandler extends DatabaseObjectDecorator implements IMessageEmbeddedObjectHandler {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\object\type\ObjectType';
	
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
		
		return array();
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
				(?:\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'|([^,\]]*))
				(?:,(?:\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'|[^,\]]*))*
			\]~ix';
		
		if (preg_match_all($pattern, $message, $matches)) {
			$results = ArrayUtil::trim($matches[1]);
			$results = array_merge($results, ArrayUtil::trim($matches[2]));
			$results = array_unique($results);
			
			return $results;
		}
		
		return array();
	}
}
