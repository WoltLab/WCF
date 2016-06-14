<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObject;

/**
 * Represents an ACP template.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Template
 *
 * @property-read	integer		$templateID
 * @property-read	integer|null	$packageID
 * @property-read	string		$templateName
 * @property-read	string		$application
 */
class ACPTemplate extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'acp_template';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'templateID';
}
