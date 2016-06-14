<?php
namespace wcf\data\search;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes search-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Search
 * 
 * @method	Search		create()
 * @method	SearchEditor[]	getObjects()
 * @method	SearchEditor	getSingleObject()
 */
class SearchAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = SearchEditor::class;
}
