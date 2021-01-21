<?php

namespace wcf\data\search\keyword;

use wcf\data\DatabaseObject;

/**
 * Represents a search keyword.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Search\Keyword
 *
 * @property-read   int     $keywordID      unique id of the search keyword
 * @property-read   string      $keyword        search keyword
 * @property-read   int     $searches       times the keyword has been searched
 * @property-read   int     $lastSearchTime     last time the keyword has been searched
 */
class SearchKeyword extends DatabaseObject
{
}
