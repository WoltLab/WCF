<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP session access logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.access.log
 * @category	Community Framework
 * 
 * @method	ACPSessionAccessLog	getDecoratedObject()
 * @mixin	ACPSessionAccessLog
 */
class ACPSessionAccessLogEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ACPSessionAccessLog::class;
}
