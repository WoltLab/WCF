<?php

namespace wcf\system\form\builder\container;

use wcf\system\condition\provider\AbstractConditionProvider;
use wcf\system\condition\type\IConditionType;
use wcf\system\form\builder\field\TDefaultIdFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @phpstan-type ConditionProvider AbstractConditionProvider<IConditionType>
 */
final class ConditionFormContainer extends FormContainer
{
    use TDefaultIdFormField;

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_conditionFormContainer';

    /**
     * @var ConditionProvider
     */
    protected AbstractConditionProvider $conditionProvider;

    public function __construct()
    {
        parent::__construct();
        $this->label("wcf.form.field.condition");
    }

    #[\Override]
    protected static function getDefaultId(): string
    {
        return 'condition';
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return isset($this->conditionProvider);
    }

    /**
     * @param ConditionProvider $conditionProvider
     *
     * @return self<ConditionProvider>
     */
    public function conditionProvider(AbstractConditionProvider $conditionProvider): self
    {
        $this->conditionProvider = $conditionProvider;

        return $this;
    }

    /**
     * @return ConditionProvider
     */
    public function getConditionProvider(): AbstractConditionProvider
    {
        if (!isset($this->conditionProvider)) {
            throw new \BadMethodCallException(
                "Condition provider has not been set yet for node '{$this->getId()}'."
            );
        }

        return $this->conditionProvider;
    }

    public function getConditionProviderClass(): string
    {
        if (!isset($this->conditionProvider)) {
            throw new \BadMethodCallException(
                "Condition provider has not been set yet for node '{$this->getId()}'."
            );
        }

        return $this->conditionProvider::class;
    }
}
