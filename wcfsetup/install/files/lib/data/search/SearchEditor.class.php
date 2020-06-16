<?php
namespace wcf\data\search;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit searches.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Search
 * 
 * @method static	Search		create(array $parameters = [])
 * @method		Search		getDecoratedObject()
 * @mixin		Search
 */
class SearchEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Search::class;
}
