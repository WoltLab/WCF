<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObject;

/**
 * Represents a template listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template.listener
 * @category	Community Framework
 *
 * @property-read	integer		$listenerID
 * @property-read	integer		$packageID
 * @property-read	string		$name
 * @property-read	string		$environment
 * @property-read	string		$templateName
 * @property-read	string		$eventName
 * @property-read	string		$templateCode
 * @property-read	integer		$niceValue
 * @property-read	string		$permissions
 * @property-read	string		$options
 */
class TemplateListener extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'template_listener';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'listenerID';
}
