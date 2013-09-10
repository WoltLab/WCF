<?php
namespace wcf\system\tagging;
use wcf\data\tag\Tag;

/**
 * Any object type that is taggable, can implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.tagging
 * @category	Community Framework
 */
interface ITaggable {
	/**
	 * Returns a list of tagged objects.
	 * 
	 * @param	wcf\data\tag\Tag	$tag
	 * @return	wcf\data\DatabaseObjectList
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
