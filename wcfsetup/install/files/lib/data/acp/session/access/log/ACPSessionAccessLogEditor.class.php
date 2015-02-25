<?php
namespace wcf\data\acp\session\access\log;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP session access logs.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.access.log
 * @category	Community Framework
 */
class ACPSessionAccessLogEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\acp\session\access\log\ACPSessionAccessLog';
}
