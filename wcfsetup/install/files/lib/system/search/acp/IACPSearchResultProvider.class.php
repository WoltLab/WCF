<?php

namespace wcf\system\search\acp;

/**
 * Every ACP search provider has to implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IACPSearchResultProvider
{
    /**
     * Returns a list of search results for given query.
     *
     * @param string $query
     * @return  ACPSearchResult[]
     */
    public function search($query);
}
