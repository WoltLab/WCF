<?php
namespace wcf\data\modification\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit modification logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Modification\Log
 * 
 * @method static	ModificationLog		create(array $parameters = [])
 * @method		ModificationLog		getDecoratedObject()
 * @mixin		ModificationLog
 */
class ModificationLogEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModificationLog::class;
}
