<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObject;

/**
 * Represents an ACP template.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.template
 * @category 	Community Framework
 */
class ACPTemplate extends DatabaseObject {
	/**
	 * @see	DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'acp_template';
	
	/**
	 * @see	DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'templateID';
}
?>