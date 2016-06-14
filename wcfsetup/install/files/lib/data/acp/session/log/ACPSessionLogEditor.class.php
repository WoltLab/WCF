<?php
namespace wcf\data\acp\session\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP session logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Session\Log
 * 
 * @method	ACPSessionLog	getDecoratedObject()
 * @mixin	ACPSessionLog
 */
class ACPSessionLogEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPSessionLog::class;
}
