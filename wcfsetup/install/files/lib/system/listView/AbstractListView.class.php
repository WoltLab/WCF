<?php

namespace wcf\system\listView;

use wcf\action\ListViewFilterAction;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\DatabaseObjectList;
use wcf\event\IPsr14Event;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\IBulkInteractionProvider;
use wcf\system\interaction\IInteractionProvider;
use wcf\system\interaction\InteractionContextMenuComponent;
use wcf\system\interaction\InteractionContextMenuComponentConfiguration;
use wcf\system\listView\filter\IListViewFilter;
use wcf\system\listView\filter\exception\InvalidFilterValue;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a list view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template TDatabaseObject of DatabaseObject|DatabaseObjectDecorator
 * @template TDatabaseObjectList of DatabaseObjectList
 */
abstract class AbstractListView
{
    private int $objectCount;
    private int $itemsPerPage = 20;
    private string $baseUrl = '';
    private string $sortField = '';
    private string $sortOrder = 'ASC';
    private string $cssClassName = '';
    private string $containerCssClassName = '';
    private int $pageNo = 1;
    private string|int|null $objectIDFilter = null;
    private ?IInteractionProvider $interactionProvider = null;
    private ?IBulkInteractionProvider $bulkInteractionProvider = null;
    private InteractionContextMenuComponent $interactionContextMenuComponent;
    private ?InteractionContextMenuComponentConfiguration $interactionContextMenuComponentConfiguration = null;
    private bool $allowFiltering = true;
    private bool $allowSorting = true;
    private bool $allowInteractions = true;
    private bool $allowBulkInteractions = true;
    private int $fixedNumberOfItems = 0;

    /**
     * @var array<string, string>
     */
    private array $activeFilters = [];

    /**
     * @var array<string, ListViewSortField>
     */
    private array $availableSortFields = [];

    /**
     * @var array<string, IListViewFilter>
     */
    private array $availableFilters = [];

    /**
     * @var TDatabaseObject[]
     */
    private array $objects;

    /**
     * @var TDatabaseObjectList
     */
    private DatabaseObjectList $objectList;

    /**
     * Returns the number of items per page.
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * Sets the number of items per page.
     */
    public function setItemsPerPage(int $itemsPerPage): void
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * Gets the fixed number of items shown.
     */
    public function getFixedNumberOfItems(): int
    {
        return $this->fixedNumberOfItems;
    }

    /**
     * Sets the maximum number of items shown.
     */
    public function setFixedNumberOfItems(int $fixedNumberOfItems): void
    {
        $this->fixedNumberOfItems = $fixedNumberOfItems;
    }

    /**
     * Sets the sort field of the list view.
     */
    public function setSortField(string $sortField): void
    {
        $this->sortField = $sortField;
    }

    /**
     * Sets the sort order of the list view.
     */
    public function setSortOrder(string $sortOrder): void
    {
        if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
            throw new \InvalidArgumentException("Invalid value '{$sortOrder}' as sort order given.");
        }

        $this->sortOrder = $sortOrder;
    }

    /**
     * Returns the sort field of the list view.
     */
    public function getSortField(): string
    {
        return $this->sortField;
    }

    /**
     * Returns the sort order of the list view.
     */
    public function getSortOrder(): string
    {
        return $this->sortOrder;
    }

    /**
     * Returns the page number.
     */
    public function getPageNo(): int
    {
        return $this->pageNo;
    }

    /**
     * Sets the page number.
     */
    public function setPageNo(int $pageNo): void
    {
        $this->pageNo = $pageNo;
    }

    /**
     * Sets the active filter values.
     *
     * @param mixed[] $filters
     */
    public function setActiveFilters(array $filters): void
    {
        $this->activeFilters = $filters;
    }

    /**
     * Returns the active filter values.
     *
     * @return mixed[]
     */
    public function getActiveFilters(): array
    {
        return $this->activeFilters;
    }

    /**
     * Sets the base url of the list view.
     */
    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    /**
     * Returns the base url of the list view.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Initializes the database object list.
     */
    protected function initObjectList(): void
    {
        $this->objectList = $this->createObjectList();
        $this->fireInitializedEvent();
        $this->validate();

        $this->objectList->sqlLimit = $this->getFixedNumberOfItems() ?: $this->getItemsPerPage();
        if (!$this->getFixedNumberOfItems()) {
            $this->objectList->sqlOffset = ($this->getPageNo() - 1) * $this->getItemsPerPage();
        }
        $this->objectList->sqlOrderBy = $this->getSqlOrderBy();
        if ($this->getObjectIDFilter() !== null) {
            $this->objectList->getConditionBuilder()->add(
                $this->objectList->getDatabaseTableAlias() . '.' . $this->objectList->getDatabaseTableIndexName() . ' = ?',
                [$this->getObjectIDFilter()]
            );
        }

        $this->applyFilters();
    }

    protected function validate(): void
    {
        if ($this->getSortField()) {
            if (!isset($this->availableSortFields[$this->getSortField()])) {
                if (\ENABLE_DEBUG_MODE) {
                    throw new \LogicException("Invalid value '{$this->getSortField()}' as sort field given.");
                } else {
                    $this->setSortField('');
                }
            }
        }
    }

    protected function getSqlOrderBy(): string
    {
        $sqlOrderBy = '';

        if ($this->getSortField()) {
            $sortFieldObject = $this->availableSortFields[$this->getSortField()];
            if ($sortFieldObject->sortByDatabaseColumn) {
                $sqlOrderBy = $sortFieldObject->sortByDatabaseColumn . ' ' . $this->getSortOrder();
            } else {
                $sqlOrderBy = $this->objectList->getDatabaseTableAlias() .
                    '.' . $sortFieldObject->id . ' ' . $this->getSortOrder();
            }

            $sqlOrderBy .= ',';
        }

        $sqlOrderBy .= $this->objectList->getDatabaseTableAlias() .
            '.' . $this->objectList->getDatabaseTableIndexName() . ' ' . $this->getSortOrder();

        return $sqlOrderBy;
    }

    /**
     * Applies the active filters.
     */
    protected function applyFilters(): void
    {
        $this->activeFilters = \array_filter($this->activeFilters, function ($value, $key) {
            if (!isset($this->availableFilters[$key])) {
                if (\ENABLE_DEBUG_MODE) {
                    throw new \LogicException("Filter applied for unknown column '{$key}'.");
                } else {
                    return false;
                }
            }

            try {
                $this->availableFilters[$key]->applyFilter($this->getObjectList(), $value);
            } catch (InvalidFilterValue $e) {
                if (\ENABLE_DEBUG_MODE) {
                    throw $e;
                } else {
                    return false;
                }
            }

            return true;
        }, \ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Returns the items for the active page.
     *
     * @return TDatabaseObject[]
     */
    public function getItems(): array
    {
        if (!isset($this->objects)) {
            $this->getObjectList()->readObjects();
            $this->objects = $this->getObjectList()->getObjects();
        }

        return $this->objects;
    }

    /**
     * Returns the total number of items.
     */
    public function countItems(): int
    {
        if (!isset($this->objectCount)) {
            $this->objectCount = $this->getObjectList()->countObjects();
            if ($this->getFixedNumberOfItems() && $this->getFixedNumberOfItems() < $this->objectCount) {
                $this->objectCount = $this->getFixedNumberOfItems();
            }
        }

        return $this->objectCount;
    }

    /**
     * Returns the database object list.
     *
     * @return TDatabaseObjectList
     */
    public function getObjectList(): DatabaseObjectList
    {
        if (!isset($this->objectList)) {
            $this->initObjectList();
        }

        return $this->objectList;
    }

    /**
     * Counts the pages of the list view.
     */
    public function countPages(): int
    {
        return (int)\ceil($this->countItems() / $this->getItemsPerPage());
    }

    /**
     * Returns the class name of this list view.
     */
    public function getClassName(): string
    {
        return \get_class($this);
    }

    /**
     * Returns true, if this list view is accessible for the active user.
     */
    public function isAccessible(): bool
    {
        return true;
    }

    /**
     * Returns the id of this list view.
     */
    public function getID(): string
    {
        $classNamePieces = \explode('\\', static::class);

        return \implode('-', $classNamePieces);
    }

    /**
     * Returns true, if the list view is filterable.
     */
    public function isFilterable(): bool
    {
        return $this->allowFiltering
            && $this->availableFilters !== [];
    }

    /**
     * Returns the endpoint for the filter action.
     */
    public function getFilterActionEndpoint(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            ListViewFilterAction::class,
            ['listView' => \get_class($this), 'listViewParameters' => $this->getParameters()]
        );
    }

    /**
     * Returns true, if the list view is sortable.
     */
    public function isSortable(): bool
    {
        return $this->allowSorting
            && $this->availableSortFields !== [];
    }

    public function addAvailableSortField(ListViewSortField $sortField): void
    {
        $this->availableSortFields[$sortField->id] = $sortField;
    }

    /**
     * @param ListViewSortField[] $sortFields
     */
    public function addAvailableSortFields(array $sortFields): void
    {
        foreach ($sortFields as $sortField) {
            $this->addAvailableSortField($sortField);
        }
    }

    public function setAllowSorting(bool $allowSorting): void
    {
        $this->allowSorting = $allowSorting;
    }

    /**
     * @return ListViewSortField[]
     */
    public function getAvailableSortFields(): array
    {
        return $this->availableSortFields;
    }

    public function addAvailableFilter(IListViewFilter $filter): void
    {
        $this->availableFilters[$filter->getId()] = $filter;
    }

    /**
     * @param IListViewFilter[] $filters
     */
    public function addAvailableFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            $this->addAvailableFilter($filter);
        }
    }

    public function setAllowFiltering(bool $allowFiltering): void
    {
        $this->allowFiltering = $allowFiltering;
    }

    /**
     * @return array<string, IListViewFilter>
     */
    public function getAvailableFilters(): array
    {
        return $this->availableFilters;
    }

    /**
     * Gets the additional parameters of the list view.
     *
     * @return mixed[]
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * Returns the label for the given filter.
     */
    public function getFilterLabel(string $id): string
    {
        if (!isset($this->availableFilters[$id])) {
            throw new \LogicException("Unknown filter '" . $id . "'.");
        }

        if (!isset($this->activeFilters[$id])) {
            throw new \LogicException("No value for filter '" . $id . "' found.");
        }

        $value = $this->availableFilters[$id]->renderValue($this->activeFilters[$id]);

        return $this->availableFilters[$id]->getLabel() . ($value !== '' ? ': ' . $value : '');
    }

    /**
     * Sets the interaction provider that is used to render the interaction context menu.
     */
    public function setInteractionProvider(IInteractionProvider $provider): void
    {
        $this->interactionProvider = $provider;
    }

    /**
     * Returns the interaction provider of the list view.
     */
    public function getInteractionProvider(): ?IInteractionProvider
    {
        return $this->interactionProvider;
    }

    /**
     * Sets the bulk interaction provider for this list view.
     */
    public function setBulkInteractionProvider(IBulkInteractionProvider $provider): void
    {
        $this->bulkInteractionProvider = $provider;
    }

    /**
     * Returns the bulk interaction provider of the list view.
     */
    public function getBulkInteractionProvider(): ?IBulkInteractionProvider
    {
        return $this->bulkInteractionProvider;
    }

    /**
     * Returns true if this list view has bulk interactions.
     */
    public function hasBulkInteractions(): bool
    {
        return $this->allowBulkInteractions
            && $this->getBulkInteractionProvider() !== null
            && $this->getBulkInteractionProvider()->getInteractions() !== [];
    }

    public function getBulkInteractionProviderClassName(): string
    {
        if (!$this->hasBulkInteractions()) {
            return '';
        }

        return \get_class($this->getBulkInteractionProvider());
    }

    public function setAllowInteractions(bool $allowInteractions): void
    {
        $this->allowInteractions = $allowInteractions;
    }

    public function setAllowBulkInteractions(bool $allowBulkInteractions): void
    {
        $this->allowBulkInteractions = $allowBulkInteractions;
    }

    /**
     * Returns true, if this list view has interactions.
     */
    public function hasInteractions(): bool
    {
        return $this->allowInteractions
            && $this->interactionProvider !== null;
    }

    /**
     * Renders the initialization code for the interactions of the list view.
     */
    public function renderInteractionInitialization(): string
    {
        if ($this->interactionProvider === null) {
            return '';
        }

        return $this->getInteractionContextMenuComponent()->renderInitialization($this->getID() . '_items');
    }

    /**
     * Renders the initialization code for the bulk interactions of the list view.
     */
    public function renderBulkInteractionInitialization(): string
    {
        if (!$this->hasBulkInteractions()) {
            return '';
        }

        return \implode("\n", \array_map(
            fn($interaction) => $interaction->renderInitialization($this->getID() . '_items'),
            $this->getBulkInteractionProvider()->getInteractions()
        ));
    }

    /**
     * Allows to define a custom configuration for the interaction context menu.
     * If no configuration is set, the default configuration will be used.
     */
    public function setInteractionContextMenuComponentConfiguration(InteractionContextMenuComponentConfiguration $configuration): void
    {
        $this->interactionContextMenuComponentConfiguration = $configuration;
    }

    /**
     * Returns the view of the interaction context menu.
     */
    public function getInteractionContextMenuComponent(): InteractionContextMenuComponent
    {
        if ($this->interactionProvider === null) {
            throw new \BadMethodCallException("Missing interaction provider.");
        }

        if (!isset($this->interactionContextMenuComponent)) {
            $this->interactionContextMenuComponent = new InteractionContextMenuComponent(
                $this->interactionProvider,
                $this->interactionContextMenuComponentConfiguration
            );
        }

        return $this->interactionContextMenuComponent;
    }

    /**
     * Renders the interactions for the given item.
     *
     * @param TDatabaseObject $item
     */
    public function renderInteractionContextMenuButton(DatabaseObject $item): string
    {
        if (!$this->hasInteractions()) {
            return '';
        }

        return $this->getInteractionContextMenuComponent()->renderButton($item);
    }

    /**
     * Filters the list view by the given object id.
     */
    public function setObjectIDFilter(string|int|null $objectID): void
    {
        $this->objectIDFilter = $objectID;
    }

    /**
     * Returns the object id by which the list view is filtered.
     */
    public function getObjectIDFilter(): string|int|null
    {
        return $this->objectIDFilter;
    }

    /**
     * Fires the initialized event.
     */
    protected function fireInitializedEvent(): void
    {
        $event = $this->getInitializedEvent();
        if ($event === null) {
            return;
        }

        EventHandler::getInstance()->fire($event);
    }

    /**
     * Returns the initialized event or null if there is no such event for this list view.
     */
    protected function getInitializedEvent(): ?IPsr14Event
    {
        return null;
    }

    public function setCssClassName(string $cssClassName): void
    {
        $this->cssClassName = $cssClassName;
    }

    public function getCssClassName(): string
    {
        return $this->cssClassName;
    }

    public function render(): string
    {
        return WCF::getTPL()->render('wcf', 'shared_listView', ['view' => $this]);
    }

    public function setContainerCssClassName(string $cssClassName): void
    {
        $this->containerCssClassName = $cssClassName;
    }

    public function getContainerCssClassName(): string
    {
        return $this->containerCssClassName;
    }

    /**
     * @return TDatabaseObjectList
     */
    protected abstract function createObjectList(): DatabaseObjectList;

    public abstract function renderItems(): string;
}
