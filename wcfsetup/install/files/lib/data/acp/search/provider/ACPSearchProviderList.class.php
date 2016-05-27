<?php
namespace wcf\data\acp\search\provider;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of ACP search providers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.acp.search.provider
 * @category	Community Framework
 *
 * @method	ACPSearchProvider		current()
 * @method	ACPSearchProvider[]		getObjects()
 * @method	ACPSearchProvider|null		search($objectID)
 * @property	ACPSearchProvider[]		$objects
 */
class ACPSearchProviderList extends DatabaseObjectList { }
