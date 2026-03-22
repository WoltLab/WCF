<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Any searchable option type should implement this interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ISearchableUserOption
{
    /**
     * Returns the html code for the search form element of this option.
     *
     * @return  string      html
     */
    public function getSearchFormElement(Option $option, mixed $value);

    /**
     * Returns a condition for search sql query.
     *
     * @return  bool
     */
    public function getCondition(PreparedStatementConditionBuilder &$conditions, Option $option, mixed $value);
}
