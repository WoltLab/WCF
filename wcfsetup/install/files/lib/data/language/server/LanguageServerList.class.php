<?php
namespace wcf\data\language\server;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of language servers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language.server
 * @category	Community Framework
 */
class LanguageServerList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\language\server\LanguageServer';
}
