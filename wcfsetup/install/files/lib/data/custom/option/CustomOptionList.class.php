<?php

namespace wcf\data\custom\option;

use wcf\data\DatabaseObjectList;

/**
 * Represents a list of options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @extends DatabaseObjectList<CustomOption>
 * @deprecated 6.2 Use `IFormOption` instead
 */
abstract class CustomOptionList extends DatabaseObjectList
{
    /**
     * @inheritDoc
     */
    public $className = CustomOption::class;

    /**
     * @inheritDoc
     */
    public $sqlOrderBy = 'showOrder';

    #[\Override]
    public function __construct()
    {
        parent::__construct();

        $this->sqlSelects = "CONCAT('customOption', CAST({$this->getDatabaseTableAlias()}.optionID AS CHAR)) AS optionName";
    }
}
