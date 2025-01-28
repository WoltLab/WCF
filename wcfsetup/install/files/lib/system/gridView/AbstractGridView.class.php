<?php

namespace wcf\system\gridView;

use LogicException;
use wcf\action\GridViewFilterAction;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\event\IPsr14Event;
use wcf\system\event\EventHandler;
use wcf\system\interaction\bulk\IBulkInteractionProvider;
use wcf\system\interaction\IInteraction;
use wcf\system\interaction\IInteractionProvider;
use wcf\system\interaction\InteractionContextMenuView;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Abstract implementation of a grid view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
abstract class AbstractGridView
{
    /**
     * @var GridViewColumn[]
     */
    private array $columns = [];

    /**
     * @var IInteraction[]
     */
    private array $quickInteractions = [];

    /**
     * @var DatabaseObject[]
     */
    protected array $objects;

    protected int $objectCount;
    protected DatabaseObjectList $objectList;
    private GridViewRowLink $rowLink;
    private int $rowsPerPage = 20;
    private string $baseUrl = '';
    private string $sortField = '';
    private string $sortOrder = 'ASC';
    private int $pageNo = 1;
    private array $activeFilters = [];
    private string|int|null $objectIDFilter = null;
    private ?IInteractionProvider $interactionProvider = null;
    private ?IBulkInteractionProvider $bulkInteractionProvider = null;
    private InteractionContextMenuView $interactionContextMenuView;

    /**
     * Adds a new column to the grid view.
     */
    public function addColumn(GridViewColumn $column): void
    {
        $this->columns[] = $column;
    }

    /**
     * Adds a new column to the grid view at the position before the specific id.
     */
    public function addColumnBefore(GridViewColumn $column, string $beforeID): void
    {
        $position = -1;

        foreach ($this->getColumns() as $key => $existingColumn) {
            if ($existingColumn->getID() === $beforeID) {
                $position = $key;
                break;
            }
        }

        if ($position === -1) {
            throw new \InvalidArgumentException("Invalid column id '{$beforeID}' given.");
        }

        array_splice($this->columns, $position, 0, [
            $column,
        ]);
    }

    /**
     * Adds a new column to the grid view at the position after the specific id.
     */
    public function addColumnAfter(GridViewColumn $column, string $afterID): void
    {
        $position = -1;

        foreach ($this->getColumns() as $key => $existingColumn) {
            if ($existingColumn->getID() === $afterID) {
                $position = $key;
                break;
            }
        }

        if ($position === -1) {
            throw new \InvalidArgumentException("Invalid column id '{$afterID}' given.");
        }

        array_splice($this->columns, $position + 1, 0, [
            $column,
        ]);
    }

    /**
     * Adds multiple new columns to the grid view.
     * @param GridViewColumn[] $columns
     */
    public function addColumns(array $columns): void
    {
        foreach ($columns as $column) {
            $this->addColumn($column);
        }
    }

    /**
     * Returns all columns of the grid view.
     * @return GridViewColumn[]
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Returns all visible (non-hidden) columns of the grid view.
     * @return GridViewColumn[]
     */
    public function getVisibleColumns(): array
    {
        return \array_filter($this->getColumns(), fn($column) => !$column->isHidden());
    }

    /**
     * Returns the column with the given id or null if no such column exists.
     */
    public function getColumn(string $id): ?GridViewColumn
    {
        foreach ($this->getColumns() as $column) {
            if ($column->getID() === $id) {
                return $column;
            }
        }

        return null;
    }

    /**
     * Returns all columns that are sortable.
     * @return GridViewColumn[]
     */
    public function getSortableColumns(): array
    {
        return \array_filter($this->getColumns(), fn($column) => $column->isSortable());
    }

    /**
     * Returns all columns that are filterable.
     * @return GridViewColumn[]
     */
    public function getFilterableColumns(): array
    {
        return \array_filter($this->getColumns(), fn($column) => $column->getFilter() !== null);
    }

    /**
     * Sets the interaction provider that is used to render the interaction context menu.
     */
    public function setInteractionProvider(IInteractionProvider $provider): void
    {
        $this->interactionProvider = $provider;
    }

    /**
     * Returns the interaction provider of the grid view.
     */
    public function getInteractionProvider(): ?IInteractionProvider
    {
        return $this->interactionProvider;
    }

    /**
     * Sets the bulk interaction provider for this grid view.
     */
    public function setBulkInteractionProvider(IBulkInteractionProvider $provider): void
    {
        $this->bulkInteractionProvider = $provider;
    }

    /**
     * Returns the bulk interaction provider of the grid view.
     */
    public function getBulkInteractionProvider(): ?IBulkInteractionProvider
    {
        return $this->bulkInteractionProvider;
    }

    public function getBulkInteractionProviderClassName(): string
    {
        if (!$this->hasBulkInteractions()) {
            return '';
        }

        return \get_class($this->getBulkInteractionProvider());
    }

    /**
     * Returns true, if this grid view has interactions.
     */
    public function hasInteractions(): bool
    {
        return $this->interactionProvider !== null || $this->quickInteractions !== [];
    }

    /**
     * Adds a quick interaction.
     */
    public function addQuickInteraction(IInteraction $interaction): void
    {
        $this->quickInteractions[] = $interaction;
    }

    /**
     * Returns true if this grid view has bulk interactions.
     */
    public function hasBulkInteractions(): bool
    {
        return $this->getBulkInteractionProvider() !== null
            && $this->getBulkInteractionProvider()->getInteractions() !== [];
    }

    /**
     * Returns the quick interactions.
     * @return IInteraction[]
     */
    public function getQuickInteractions(): array
    {
        return $this->quickInteractions;
    }

    /**
     * Renders the grid view and returns the HTML code.
     */
    public function render(): string
    {
        return WCF::getTPL()->fetch('shared_gridView', 'wcf', ['view' => $this], true);
    }

    /**
     * Renders the rows and returns the HTML code.
     */
    public function renderRows(): string
    {
        $this->prepareRenderers();

        return WCF::getTPL()->fetch('shared_gridViewRows', 'wcf', ['view' => $this], true);
    }

    /**
     * Renders the given grid view column.
     */
    public function renderColumn(GridViewColumn $column, DatabaseObject $row): string
    {
        $value = $this->getData($row, $column->getID());
        if ($column->encodeValue()) {
            $value = StringUtil::encodeHTML($value);
        }

        $value = $column->render($value, $row);

        if (isset($this->rowLink) && $column->applyRowLink()) {
            $value = $this->rowLink->render($value, $row, $column->isTitleColumn());
        }

        return $value;
    }

    /**
     * Returns the view of the interaction context menu.
     */
    public function getInteractionContextMenuView(): InteractionContextMenuView
    {
        if ($this->interactionProvider === null) {
            throw new \BadMethodCallException("Missing interaction provider.");
        }

        if (!isset($this->interactionContextMenuView)) {
            $this->interactionContextMenuView = new InteractionContextMenuView($this->interactionProvider);
        }

        return $this->interactionContextMenuView;
    }

    /**
     * Renders the initialization code for the interactions of the grid view.
     */
    public function renderInteractionInitialization(): string
    {
        $code = '';
        if ($this->interactionProvider !== null) {
            $code = $this->getInteractionContextMenuView()->renderInitialization($this->getID() . '_table');
        }

        if ($this->quickInteractions !== []) {
            $code .= "\n";
            $code .= \implode("\n", \array_map(
                fn($interaction) => $interaction->renderInitialization($this->getID() . '_table'),
                $this->getQuickInteractions()
            ));
        }

        return $code;
    }

    /**
     * Renders the initialization code for the bulk interactions of the grid view.
     */
    public function renderBulkInteractionInitialization(): string
    {
        if (!$this->hasBulkInteractions()) {
            return '';
        }

        return \implode("\n", \array_map(
            fn($interaction) => $interaction->renderInitialization($this->getID() . '_table'),
            $this->getBulkInteractionProvider()->getInteractions()
        ));
    }

    /**
     * Renders the interactions for the given row.
     */
    public function renderInteractionContextMenuButton(DatabaseObject $row): string
    {
        if ($this->interactionProvider === null) {
            return '';
        }

        return $this->getInteractionContextMenuView()->renderButton($row);
    }

    /**
     * Renders the interactions for the given row.
     */
    public function renderQuickInteractions(DatabaseObject $row): string
    {
        return \implode("\n", \array_map(
            static fn($interaction) => $interaction->render($row),
            $this->getQuickInteractions()
        ));
    }

    /**
     * Returns the row data for the given identifier.
     */
    protected function getData(DatabaseObject $row, string $identifer): mixed
    {
        return $row->__get($identifer);
    }

    /**
     * Counts the pages of the grid view.
     */
    public function countPages(): int
    {
        return \ceil($this->countRows() / $this->getRowsPerPage());
    }

    /**
     * Returns the class name of this grid view.
     */
    public function getClassName(): string
    {
        return \get_class($this);
    }

    /**
     * Returns true, if this grid view is accessible for the active user.
     */
    public function isAccessible(): bool
    {
        return true;
    }

    /**
     * Returns the id of this grid view.
     */
    public function getID(): string
    {
        $classNamePieces = \explode('\\', static::class);

        return \implode('-', $classNamePieces);
    }

    /**
     * Sets the base url of the grid view.
     */
    public function setBaseUrl(string $url): void
    {
        $this->baseUrl = $url;
    }

    /**
     * Returns the base url of the grid view.
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Sets the sort field of the grid view.
     */
    public function setSortField(string $sortField): void
    {
        if (!\in_array($sortField, \array_map(fn($column) => $column->getID(), $this->getSortableColumns()))) {
            throw new \InvalidArgumentException("Invalid value '{$sortField}' as sort field given.");
        }

        $this->sortField = $sortField;
    }

    /**
     * Sets the sort order of the grid view.
     */
    public function setSortOrder(string $sortOrder): void
    {
        if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
            throw new \InvalidArgumentException("Invalid value '{$sortOrder}' as sort order given.");
        }

        $this->sortOrder = $sortOrder;
    }

    /**
     * Returns the sort field of the grid view.
     */
    public function getSortField(): string
    {
        return $this->sortField;
    }

    /**
     * Returns the sort order of the grid view.
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
     * Returns the number of rows per page.
     */
    public function getRowsPerPage(): int
    {
        return $this->rowsPerPage;
    }

    /**
     * Sets the number of rows per page.
     */
    public function setRowsPerPage(int $rowsPerPage): void
    {
        $this->rowsPerPage = $rowsPerPage;
    }

    /**
     * Returns true, if the grid view is filterable.
     */
    public function isFilterable(): bool
    {
        return $this->getFilterableColumns() !== [];
    }

    /**
     * Returns the endpoint for the filter action.
     */
    public function getFilterActionEndpoint(): string
    {
        return LinkHandler::getInstance()->getControllerLink(
            GridViewFilterAction::class,
            ['gridView' => \get_class($this)]
        );
    }

    /**
     * Sets the active filter values.
     */
    public function setActiveFilters(array $filters): void
    {
        $this->activeFilters = $filters;
    }

    /**
     * Returns the active filter values.
     */
    public function getActiveFilters(): array
    {
        return $this->activeFilters;
    }

    /**
     * Returns the label for the given filter.
     */
    public function getFilterLabel(string $id): string
    {
        $column = $this->getColumn($id);
        if (!$column) {
            throw new LogicException("Unknown column '" . $id . "'.");
        }

        if (!$column->getFilter()) {
            throw new LogicException("Column '" . $id . "' has no filter.");
        }

        if (!isset($this->activeFilters[$id])) {
            throw new LogicException("No value for filter '" . $id . "' found.");
        }

        $value = $column->getFilter()->renderValue($this->activeFilters[$id]);

        return $column->getLabel() . ($value !== '' ? ': ' . $value : '');
    }

    /**
     * Gets the additional parameters of the grid view.
     */
    public function getParameters(): array
    {
        return [];
    }

    /**
     * Adds the given row link to the grid view.
     */
    public function addRowLink(GridViewRowLink $rowLink): void
    {
        $this->rowLink = $rowLink;
    }

    /**
     * Returns the id for the given row.
     */
    public function getObjectID(DatabaseObject $row): string|int
    {
        return $row->getObjectID();
    }

    /**
     * Filters the grid view by the given object id.
     */
    public function setObjectIDFilter(string|int|null $objectID): void
    {
        $this->objectIDFilter = $objectID;
    }

    /**
     * Returns the object id by which the grid view is filtered.
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
     * Validates the configuration of this grid view.
     */
    protected function validate(): void
    {
        $titleColumn = null;

        foreach ($this->getColumns() as $column) {
            if ($column->isTitleColumn()) {
                if ($titleColumn !== null) {
                    throw new \InvalidArgumentException("More than one title column defined in grid view with id '{$this->getID()}'.");
                }

                $titleColumn = $column;
            }
        }

        if ($titleColumn === null) {
            throw new \InvalidArgumentException("Missing title column in grid view with id '{$this->getID()}'.");
        }
    }

    /**
     * Returns the initialized event or null if there is no such event for this grid view.
     */
    protected function getInitializedEvent(): ?IPsr14Event
    {
        return null;
    }

    /**
     * Prepares the column renderers.
     */
    protected function prepareRenderers(): void
    {
        foreach ($this->getVisibleColumns() as $column) {
            foreach ($column->getRenderers() as $renderer) {
                foreach ($this->getRows() as $row) {
                    $renderer->prepare($this->getData($row, $column->getID()), $row);
                }
            }
        }
    }

    /**
     * Returns the rows for the active page.
     */
    public function getRows(): array
    {
        if (!isset($this->objects)) {
            $this->getObjectList()->readObjects();
            $this->objects = $this->getObjectList()->getObjects();
        }

        return $this->objects;
    }

    /**
     * Returns the total number of rows.
     */
    public function countRows(): int
    {
        if (!isset($this->objectCount)) {
            $this->objectCount = $this->getObjectList()->countObjects();
        }

        return $this->objectCount;
    }

    /**
     * Returns the database object list.
     */
    public function getObjectList(): DatabaseObjectList
    {
        if (!isset($this->objectList)) {
            $this->initObjectList();
        }

        return $this->objectList;
    }

    /**
     * Initializes the database object list.
     */
    protected function initObjectList(): void
    {
        $this->objectList = $this->createObjectList();
        $this->objectList->sqlLimit = $this->getRowsPerPage();
        $this->objectList->sqlOffset = ($this->getPageNo() - 1) * $this->getRowsPerPage();
        if ($this->getSortField()) {
            $column = $this->getColumn($this->getSortField());
            if ($column && $column->getSortByDatabaseColumn()) {
                $this->objectList->sqlOrderBy = $column->getSortByDatabaseColumn() . ' ' . $this->getSortOrder();
            } else {
                $this->objectList->sqlOrderBy = $this->objectList->getDatabaseTableAlias() .
                    '.' . $this->getSortField() . ' ' . $this->getSortOrder();
            }

            $this->objectList->sqlOrderBy .= ',' . $this->objectList->getDatabaseTableAlias() .
                '.' . $this->objectList->getDatabaseTableIndexName() . ' ' . $this->getSortOrder();
        }
        if ($this->getObjectIDFilter() !== null) {
            $this->objectList->getConditionBuilder()->add(
                $this->objectList->getDatabaseTableAlias() . '.' . $this->objectList->getDatabaseTableIndexName() . ' = ?',
                [$this->getObjectIDFilter()]
            );
        }
        $this->applyFilters();
        $this->validate();
        $this->fireInitializedEvent();
    }

    /**
     * Applies the active filters.
     */
    protected function applyFilters(): void
    {
        foreach ($this->getActiveFilters() as $key => $value) {
            $column = $this->getColumn($key);
            if (!$column) {
                throw new LogicException("Unknown column '" . $key . "'");
            }

            $column->getFilter()->applyFilter($this->getObjectList(), $column->getID(), $value);
        }
    }

    /**
     * Creates the database object list of this grid view.
     */
    protected abstract function createObjectList(): DatabaseObjectList;
}
