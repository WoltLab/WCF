<?php
namespace wcf\data\package\update\version;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes package update version-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.version
 * @category	Community Framework
 * 
 * @method	PackageUpdateVersion		create()
 * @method	PackageUpdateVersionEditor[]	getObjects()
 * @method	PackageUpdateVersionEditor	getSingleObject()
 */
class PackageUpdateVersionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PackageUpdateVersionEditor::class;
}
