<?php
namespace wcf\system\message\embedded\object;

/**
 * Default interface of simple embedded object handler.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Message\Embedded\Object
 */
interface ISimpleMessageEmbeddedObjectHandler extends IMessageEmbeddedObjectHandler {
	/**
	 * Validates the provided values for existence and returns the filtered list.
	 * 
	 * @param       string          $objectType     object type identifier
	 * @param       int         $objectID       object id
	 * @param       int[]       $values         list of value ids
	 * @return      int[]       filtered list
	 */
	public function validateValues($objectType, $objectID, array $values);
	
	/**
	 * Returns replacement string for simple placeholders. Must return `null`
	 * if no replacement should be performed due to invalid or missing arguments.
	 * 
	 * @param       string          $objectType     object type identifier
	 * @param       int         $objectID       object id
	 * @param       int         $value          value id
	 * @param       array           $attributes     list of additional attributes
	 * @return      string|null     replacement string or null if value id is unknown
	 */
	public function replaceSimple($objectType, $objectID, $value, array $attributes);
}
