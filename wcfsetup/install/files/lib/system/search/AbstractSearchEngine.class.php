<?php

namespace wcf\system\search;

use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;

/**
 * Default implementation for search engines, this class should be extended by
 * all search engines to preserve compatibility in case of interface changes.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Search
 */
abstract class AbstractSearchEngine extends SingletonFactory implements ISearchEngine
{
    /**
     * class name for preferred condition builder
     * @var string
     */
    protected $conditionBuilderClassName = PreparedStatementConditionBuilder::class;

    /**
     * list of engine-specific special characters
     * @var string[]
     */
    protected $specialCharacters = [];

    /**
     * @inheritDoc
     */
    public function getConditionBuilderClassName()
    {
        return $this->conditionBuilderClassName;
    }

    /**
     * @deprecated 5.4 - This method is dangerous. See https://github.com/WoltLab/WCF/issues/3815.
     */
    protected function parseSearchQuery($query)
    {
        throw new \BadMethodCallException('Using the generic `parseSearchQuery()` method is dangerous. See WoltLab/WCF#3815 (https://github.com/WoltLab/WCF/issues/3815).');
    }

    /**
     * @deprecated 5.4 - This method was required for use in parseSearchQuery().
     */
    abstract protected function getFulltextMinimumWordLength();

    /**
     * @inheritDoc
     */
    public function removeSpecialCharacters($string)
    {
        if (!empty($this->specialCharacters)) {
            return \str_replace($this->specialCharacters, '', $string);
        }

        return $string;
    }
}
