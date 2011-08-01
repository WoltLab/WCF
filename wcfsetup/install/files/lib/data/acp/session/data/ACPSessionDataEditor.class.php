<?php
namespace wcf\data\acp\session\data;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit ACP session data.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.session.data
 * @category 	Community Framework
 */
class ACPSessionDataEditor extends DatabaseObjectEditor {
	/**
	 * @see	wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\acp\session\data\ACPSessionData';
}
