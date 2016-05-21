<?php
namespace wcf\data\acp\search\provider;
use wcf\data\DatabaseObject;

/**
 * Represents an ACP search provider.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.search.provider
 * @category	Community Framework
 *
 * @property-read	integer		$providerID
 * @property-read	integer		$packageID
 * @property-read	string		$providerName
 * @property-read	string		$className
 * @property-read	integer		$showOrder
 */
class ACPSearchProvider extends DatabaseObject {
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableName = 'acp_search_provider';
	
	/**
	 * @inheritDoc
	 */
	protected static $databaseTableIndexName = 'providerID';
}
