<?php
namespace wcf\data\clipboard\action;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit clipboard actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.clipboard.action
 * @category	Community Framework
 */
class ClipboardActionEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\clipboard\action\ClipboardAction';
}
