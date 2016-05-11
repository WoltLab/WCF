<?php
namespace wcf\data\package\update;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit package updates.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category	Community Framework
 *
 * @method	PackageUpdate	getDecoratedObject()
 * @mixin	PackageUpdate
 */
class PackageUpdateEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PackageUpdate::class;
}
