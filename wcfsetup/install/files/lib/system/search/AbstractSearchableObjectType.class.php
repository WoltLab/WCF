<?php

namespace wcf\system\search;

use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\form\IForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * This class provides default implementations for the ISearchableObjectType interface.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 5.5
 */
abstract class AbstractSearchableObjectType extends AbstractObjectTypeProcessor implements ISearchableObjectType
{
    #[\Override]
    public function show(?IForm $form = null)
    {
    }

    #[\Override]
    public function getApplication()
    {
        $classParts = \explode('\\', static::class);

        return $classParts[0];
    }

    #[\Override]
    public function getConditions(?IForm $form = null)
    {
        return null;
    }

    #[\Override]
    public function getJoins()
    {
        return '';
    }

    #[\Override]
    public function getSubjectFieldName()
    {
        return $this->getTableName() . '.subject';
    }

    #[\Override]
    public function getUsernameFieldName()
    {
        return $this->getTableName() . '.username';
    }

    #[\Override]
    public function getTimeFieldName()
    {
        return $this->getTableName() . '.time';
    }

    #[\Override]
    public function getAdditionalData()
    {
        return null;
    }

    #[\Override]
    public function isAccessible()
    {
        return true;
    }

    #[\Override]
    public function getFormTemplateName()
    {
        return '';
    }

    #[\Override]
    public function getOuterSQLQuery(
        string $q,
        ?PreparedStatementConditionBuilder &$searchIndexConditions = null,
        ?PreparedStatementConditionBuilder &$additionalConditions = null
    ) {
        return '';
    }

    #[\Override]
    public function setLocation()
    {
    }

    #[\Override]
    public function getActiveMenuItem()
    {
        return '';
    }
}
