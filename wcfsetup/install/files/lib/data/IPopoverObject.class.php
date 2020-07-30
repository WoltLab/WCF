<?php
namespace wcf\data;

/**
 * Database objects that support links with preview popovers via `WoltLabSuite/Core/Controller/Popover`
 * can implement this interface so that when the `anchor` template plugin is used and the CSS class name
 * returned by `getPopoverLinkClass()` is given, the `data-object-id` attribute is automatically added
 * to support the preview popover.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 * @since	5.3
 */
interface IPopoverObject extends IIDObject, ITitledLinkObject {
	/**
	 * Returns the CSS class that objects of this type use for popover links.
	 * 
	 * @return	string
	 */
	public function getPopoverLinkClass();
}
