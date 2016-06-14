<?php
namespace wcf\system\tagging;

/**
 * Any tagged object has to implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 */
interface ITagged {
	/**
	 * Gets the id of the tagged object.
	 * 
	 * @return	integer		the id to get
	 */
	public function getObjectID();
	
	/**
	 * Gets the taggable type of this tagged object.
	 * 
	 * @return	\wcf\system\tagging\ITaggable
	 */
	public function getTaggable();
}
