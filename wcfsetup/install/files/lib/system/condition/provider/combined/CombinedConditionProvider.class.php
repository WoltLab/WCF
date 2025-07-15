<?php

namespace wcf\system\condition\provider\combined;

use wcf\system\condition\provider\AbstractConditionProvider;
use wcf\system\condition\type\IConditionType;

/**
 * Combining multiple condition providers.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @template TConditionType of IConditionType
 * @template TConditionProvider of AbstractConditionProvider
 * @extends AbstractConditionProvider<TConditionType>
 */
abstract class CombinedConditionProvider extends AbstractConditionProvider
{
    /**
     * @param TConditionProvider ...$providers
     */
    public function __construct(AbstractConditionProvider ...$providers)
    {
        foreach ($providers as $provider) {
            foreach ($provider->getConditionTypes() as $condition) {
                $this->addCondition($condition);
            }
        }
    }
}
