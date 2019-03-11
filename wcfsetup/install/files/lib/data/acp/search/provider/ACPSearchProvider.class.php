<?php
namespace wcf\data\acp\search\provider;
use wcf\data\DatabaseObject;

/**
 * Represents an ACP search provider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Acp\Search\Provider
 *
 * @property-read	integer		$providerID		unique id of the ACP search provider
 * @property-read	integer		$packageID		id of the package which delivers the ACP search provider
 * @property-read	string		$providerName		textual identifier of the ACP search provider
 * @property-read	string		$className		class name of the `wcf\system\search\acp\IACPSearchResultProvider` implementation executing the search
 * @property-read	integer		$showOrder		position of the grouped results of the ACP search provider within the result list
 */
class ACPSearchProvider extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'acp_search_provider';
}
