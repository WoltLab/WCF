<?php
namespace wcf\data\search\keyword;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit keywords.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Search\Keyword
 * 
 * @method static	SearchKeyword	create(array $parameters = [])
 * @method		SearchKeyword	getDecoratedObject()
 * @mixin		SearchKeyword
 */
class SearchKeywordEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = SearchKeyword::class;
}
