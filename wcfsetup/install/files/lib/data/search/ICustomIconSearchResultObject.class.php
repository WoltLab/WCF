<?php
namespace wcf\data\search;

/**
 * Extends the base search result objects with the ability to provide a custom image or icon
 * class name instead of the default avatar/icon. 
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Data\Search
 * @since       3.2
 */
interface ICustomIconSearchResultObject extends ISearchResultObject {
	/**
	 * Returns either a FontAwesome icon name including the `fa-` prefix or
	 * a string that is interpreted as an URL to an image that can be scaled
	 * to 48x48. Returning an empty string will trigger the default behavior. 
	 * 
	 * @return      string
	 */
	public function getCustomSearchResultIcon(): string;
}
