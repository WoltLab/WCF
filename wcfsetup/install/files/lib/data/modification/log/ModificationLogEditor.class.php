<?php
namespace wcf\data\modification\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit modification logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.modification.log
 * @category	Community Framework
 * 
 * @method	ModificationLog		getDecoratedObject()
 * @mixin	ModificationLog
 */
class ModificationLogEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModificationLog::class;
}
