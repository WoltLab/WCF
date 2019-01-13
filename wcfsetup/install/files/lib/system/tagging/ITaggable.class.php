<?php
namespace wcf\system\tagging;
use wcf\data\DatabaseObjectList;
use wcf\data\tag\Tag;

/**
 * Any object type that is taggable, can implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Tagging
 */
interface ITaggable {
	/**
	 * Returns a list of tagged objects.
	 * 
	 * @param	Tag	$tag
	 * @return	DatabaseObjectList
	 * @deprecated 5.2
	 */
	public function getObjectList(Tag $tag);
	
	/**
	 * Returns the template name for the result output.
	 * 
	 * @return	string
	 */
	public function getTemplateName();
	
	/**
	 * Returns the application of the result template.
	 * 
	 * @return	string
	 */
	public function getApplication();
}
