<?php
namespace wcf\system\tagging;
use wcf\data\IIDObject;

/**
 * Any tagged object has to implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 */
interface ITagged extends IIDObject {
	/**
	 * Returns the taggable type of this tagged object.
	 * 
	 * @return	ITaggable
	 */
	public function getTaggable();
}
