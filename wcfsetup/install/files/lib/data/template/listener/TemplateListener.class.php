<?php
namespace wcf\data\template\listener;
use wcf\data\DatabaseObject;

/**
 * Represents a template listener.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template\Listener
 *
 * @property-read	integer		$listenerID		unique id of the template listener
 * @property-read	integer		$packageID		id of the package which delivers the template listener
 * @property-read	string		$name			name of the template listener
 * @property-read	string		$environment		environment in which the template listener is executed, possible values: 'user' or 'admin'
 * @property-read	string		$templateName		name of the template in which the listened event is fired
 * @property-read	string		$eventName		name of the listened event
 * @property-read	string		$templateCode		included template code at the position of the listened event
 * @property-read	integer		$niceValue		value from [-128, 127] used to determine template listener execution order (template listeners with smaller `$niceValue` are executed first)
 * @property-read	string		$permissions		comma separated list of user group permissions of which the active user needs to have at least one for the template listener to be executed
 * @property-read	string		$options		comma separated list of options of which at least one needs to be enabled for the template listener to be executed
 */
class TemplateListener extends DatabaseObject {}
