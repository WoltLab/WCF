<?php

namespace wcf\data\label\group;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\ITraversableObject;
use wcf\data\label\Label;
use wcf\data\user\User;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Represents a viewable label group.
 *
 * @author  Alexander Ebert, Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @mixin   LabelGroup
 * @extends DatabaseObjectDecorator<LabelGroup>
 * @implements ITraversableObject<Label>
 */
class ViewableLabelGroup extends DatabaseObjectDecorator implements \Countable, ITraversableObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = LabelGroup::class;

    /**
     * list of labels
     * @var Label[]
     */
    protected $labels = [];

    /**
     * list of permissions by type
     * @var int[][][]
     */
    protected $permissions = [
        'group' => [],
        'user' => [],
    ];

    /**
     * current iterator index
     * @var int
     */
    protected $index = 0;

    /**
     * list of index to object relation
     * @var int[]|null
     */
    protected $indexToObject;

    /**
     * Adds a label.
     *
     * @return void
     */
    public function addLabel(Label $label)
    {
        $this->labels[$label->labelID] = $label;
        $this->indexToObject[] = $label->labelID;
    }

    /**
     * Sets group permissions.
     *
     * @param mixed[] $permissions
     * @return void
     */
    public function setGroupPermissions(array $permissions)
    {
        $this->permissions['group'] = $permissions;
    }

    /**
     * Sets user permissions.
     *
     * @param mixed[] $permissions
     * @return void
     */
    public function setUserPermissions(array $permissions)
    {
        $this->permissions['user'] = $permissions;
    }

    /**
     * Returns true, if label is known.
     *
     * @return  bool
     */
    public function isValid(int $labelID)
    {
        return isset($this->labels[$labelID]);
    }

    /**
     * Returns true, if the given user fulfils option id permissions.
     * If the user parameter is null, the method checks the current user.
     *
     * @return  bool
     */
    public function getPermission(int $optionID, ?User $user = null)
    {
        if ($user === null) {
            $user = WCF::getUser();
        }

        // validate by user id
        if (!$user->isGuest()) {
            if (
                isset($this->permissions['user'][$user->userID])
                && isset($this->permissions['user'][$user->userID][$optionID])
            ) {
                // @phpstan-ignore equal.notAllowed
                if ($this->permissions['user'][$user->userID][$optionID] == 1) {
                    return true;
                }
            }
        }

        // validate by group id
        foreach ($user->getGroupIDs() as $groupID) {
            if (
                isset($this->permissions['group'][$groupID])
                && isset($this->permissions['group'][$groupID][$optionID])
            ) {
                // @phpstan-ignore equal.notAllowed
                if ($this->permissions['group'][$groupID][$optionID] == 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Returns a list of label ids.
     *
     * @return int[]
     */
    public function getLabelIDs()
    {
        return \array_keys($this->labels);
    }

    /**
     * Returns a list of labels.
     *
     * @return Label[]
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * Returns a label by id.
     *
     * @return ?Label
     */
    public function getLabel(int $labelID)
    {
        return $this->labels[$labelID] ?? null;
    }

    #[\Override]
    public function count(): int
    {
        return \count($this->labels);
    }

    #[\Override]
    #[\ReturnTypeWillChange]
    public function current()
    {
        $objectID = $this->indexToObject[$this->index];

        return $this->labels[$objectID];
    }

    /**
     * CAUTION: This methods does not return the current iterator index,
     * rather than the object key which maps to that index.
     */
    #[\Override]
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->indexToObject[$this->index];
    }

    #[\Override]
    public function next(): void
    {
        $this->index++;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->index = 0;
    }

    #[\Override]
    public function valid(): bool
    {
        return isset($this->indexToObject[$this->index]);
    }

    #[\Override]
    public function seek(int $offset): void
    {
        $this->index = $offset;

        if (!$this->valid()) {
            throw new \OutOfBoundsException();
        }
    }

    #[\Override]
    public function seekTo(int $objectID)
    {
        $this->index = \array_search($objectID, $this->indexToObject, true);

        if ($this->index === false) {
            throw new SystemException("object id '" . $objectID . "' is invalid");
        }
    }

    #[\Override]
    public function search(int $objectID)
    {
        try {
            $this->seekTo($objectID);

            return $this->current();
        } catch (SystemException $e) {
            return null;
        }
    }

    /**
     * Sorts the labels alphabetically by their translated name using locale-aware comparison.
     *
     * @since 6.3
     */
    public function sortLabelsAlphabetically(): void
    {
        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $this->labels,
            static fn(Label $a, Label $b) => $collator->compare($a->getTitle(), $b->getTitle())
        );
        $this->indexToObject = \array_keys($this->labels);
    }

    /**
     * Renders a `<woltlab-core-label-picker>` to select a label of this group.
     *
     * The selected value is `0` or `null` for no selection, `-1` for objects
     * without any label of this group (requires `$invertible`) or a label id.
     * Invalid values are treated as no selection.
     *
     * @since 6.3
     */
    public function renderPicker(
        string $name,
        ?int $value = null,
        bool $required = false,
        bool $invertible = false,
        string $id = '',
    ): string {
        $labels = [];
        foreach ($this->labels as $label) {
            $labels[] = [$label->labelID, $label->render()];
        }

        if ($value === null || ($value === -1 ? !$invertible : !$this->isValid($value))) {
            $value = 0;
        }

        $attributes = [
            'name' => $name,
            'labels' => JSON::encode($labels),
            'value' => $value,
        ];
        if ($id !== '') {
            $attributes['id'] = $id;
        }

        $html = '';
        foreach ($attributes as $attribute => $attributeValue) {
            $html .= \sprintf(' %s="%s"', $attribute, StringUtil::encodeHTML((string)$attributeValue));
        }
        if ($required) {
            $html .= ' required';
        }
        if ($invertible) {
            $html .= ' invertible';
        }

        return "<woltlab-core-label-picker{$html}></woltlab-core-label-picker>";
    }

    /**
     * Returns true if any permissions have been set for this label group.
     *
     * @return  bool
     */
    public function hasPermissions()
    {
        return !empty($this->permissions['group']) || !empty($this->permissions['user']);
    }
}
