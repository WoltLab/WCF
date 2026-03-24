<?php

namespace wcf\system\condition;

use wcf\data\condition\Condition;

/**
 * Condition implementation for selecting multiple page controllers.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  3.0
 */
class MultiPageControllerCondition extends AbstractMultiSelectCondition implements IContentCondition
{
    #[\Override]
    protected function getFieldElement()
    {
        return '';
    }

    #[\Override]
    protected function getOptions()
    {
        return [];
    }

    #[\Override]
    public function showContent(Condition $condition)
    {
        return false;
    }
}
