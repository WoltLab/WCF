<?php
namespace wcf\data\acp\session\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP session logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.log
 * @category	Community Framework
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
