<?php
namespace wcf\data\page\content;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of page content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page\Content
 * @since	3.0
 *
 * @method	PageContent		current()
 * @method	PageContent[]	        getObjects()
 * @method	PageContent|null	search($objectID)
 * @property	PageContent[]	        $objects
 */
class PageContentList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = PageContent::class;
}
