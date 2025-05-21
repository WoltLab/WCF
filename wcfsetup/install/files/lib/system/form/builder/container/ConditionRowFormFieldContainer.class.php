<?php

namespace wcf\system\form\builder\container;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 */
final class ConditionRowFormFieldContainer extends FormContainer
{
    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_conditionRowFormFieldContainer';

    private string $conditionType;
    private string $containerId;
    private int $conditionIndex = 0;

    public function __construct()
    {
        $this->addClass("condition-row");
    }

    /**
     * Sets the condition index for the container.
     */
    public function conditionIndex(int $conditionIndex): self
    {
        $this->conditionIndex = $conditionIndex;

        return $this;
    }

    /**
     * Retrieves the condition index of the container.
     */
    public function getConditionIndex(): int
    {
        return $this->conditionIndex;
    }

    /**
     * Sets the condition type for the container.
     */
    public function conditionType(string $conditionType): self
    {
        $this->conditionType = $conditionType;

        return $this;
    }

    /**
     * Retrieves the condition type of the container.
     */
    public function getConditionType(): string
    {
        return $this->conditionType;
    }

    /**
     * Sets the container ID.
     */
    public function containerId(string $containerId): self
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Retrieves the container ID.
     */
    public function getContainerId(): string
    {
        return $this->containerId;
    }
}
