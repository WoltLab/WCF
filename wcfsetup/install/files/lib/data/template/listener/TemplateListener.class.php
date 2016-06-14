<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObject;

/**
 * Represents a template listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template\Listener
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
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'template_listener';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'listenerID';
}
