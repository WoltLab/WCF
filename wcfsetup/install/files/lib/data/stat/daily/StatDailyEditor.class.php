<?php
namespace wcf\data\stat\daily;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to create, edit and delete a stat daily entry.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Stat\Daily
 *
 * @method	StatDaily	getDecoratedObject()
 * @mixin	StatDaily
 */
class StatDailyEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = StatDaily::class;
}
