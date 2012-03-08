<?php
namespace wcf\system\user\collapsible\content;

/**
 * Provides basic methods to toggle container content.
 * 
 * @author 	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.user.collapsible.content
 * @category 	Community Framework
 */
interface ICollapsibleContentAction {
	/**
	 * Validates required parameters.
	 */
	public function validateLoadContainer();
	
	/**
	 * Toggles the visibility of container content.
	 */
	public function loadContainer();
}
