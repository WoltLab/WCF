<?php
namespace wcf\data\acp\template;
use wcf\data\DatabaseObject;

/**
 * Represents an ACP template.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Template
 *
 * @property-read	integer		$templateID	unique id of the acp template
 * @property-read	integer|null	$packageID	id of the package which delivers the acp template
 * @property-read	string		$templateName	name of the template
 * @property-read	string		$application	abbreviation of the application to which the template belongs
 */
class ACPTemplate extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'templateID';
}
