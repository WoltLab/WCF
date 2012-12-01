<?php
namespace wcf\system\clipboard\action;

/**
 * Abstract implementation of clipboard action handler.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.clipboard.action
 * @category	Community Framework
 */
abstract class AbstractClipboardAction implements IClipboardAction {
	/**
	 * @see	wcf\system\clipboard\action\IClipboardAction::filterObjects()
	 */
	public function filterObjects(array $objects, array $typeData) {
		return $objects;
	}
}
