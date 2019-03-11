<?php
namespace wcf\data\page\content;

/**
 * Represents a list of page content as search results.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page\Content
 * @since	3.1
 * 
 * @method	SearchResultPageContent		        current()
 * @method	SearchResultPageContent[]		getObjects()
 * @method	SearchResultPageContent|null		search($objectID)
 * @property	SearchResultPageContent[]		$objects
 */
class SearchResultPageContentList extends PageContentList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = SearchResultPageContent::class;
}
