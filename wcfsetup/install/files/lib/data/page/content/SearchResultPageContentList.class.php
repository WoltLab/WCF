<?php
declare(strict_types=1);
namespace wcf\data\page\content;

/**
 * Represents a list of page content as search results.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
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
