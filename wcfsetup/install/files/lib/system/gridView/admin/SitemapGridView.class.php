<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\SitemapEditForm;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\ILinkableObject;
use wcf\event\gridView\admin\SitemapGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\sitemap\object\RegisteredSitemapObject;
use wcf\system\sitemap\SitemapHandler;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Grid view for the list of sitemap objects.
 *
 * The rows of this grid view are not backed by a database table, they are
 * created from the sitemap objects provided by `SitemapHandler`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractGridView<DatabaseObject, DatabaseObjectList>
 */
final class SitemapGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('name')
                ->label('wcf.acp.sitemap')
                ->titleColumn()
                ->sortable(),
            GridViewColumn::for('rebuildTime')
                ->label('wcf.acp.sitemap.rebuildTime')
                ->renderer(new class extends DefaultColumnRenderer {
                    #[\Override]
                    public function render(mixed $value, DatabaseObject $row): string
                    {
                        $start = DateUtil::getDateTimeByTimestamp(\TIME_NOW);
                        $end = DateUtil::getDateTimeByTimestamp(\TIME_NOW + (int)$value);

                        return DateUtil::formatInterval($end->diff($start), true, DateUtil::FORMAT_PLAIN);
                    }
                }),
        ]);

        $this->addQuickInteraction(new ToggleInteraction(
            'sitemapObjectToggle',
            'core/sitemaps/%s/enable',
            'core/sitemaps/%s/disable',
        ));
        $this->addRowLink(new GridViewRowLink(isLinkableObject: true));
        $this->setDefaultSortField('name');
        $this->setRowsPerPage(50);
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->hasPermission('admin.management.canRebuildData');
    }

    #[\Override]
    public function getRows(): array
    {
        if (!isset($this->objects)) {
            $this->getObjectList();
        }

        return $this->objects;
    }

    #[\Override]
    public function countRows(): int
    {
        if (!isset($this->objectCount)) {
            $this->getObjectList();
        }

        return $this->objectCount;
    }

    #[\Override]
    protected function initObjectList(): void
    {
        $this->objectList = $this->createObjectList();
        $this->fireInitializedEvent();
        $this->validate();

        $objects = $this->loadDataSource();

        $objectIDFilter = $this->getObjectIDFilter();
        if ($objectIDFilter !== null) {
            $objects = \array_filter(
                $objects,
                static fn (string $objectName) => $objectName === (string)$objectIDFilter,
                \ARRAY_FILTER_USE_KEY
            );
        }

        $this->objectCount = \count($objects);

        if ($this->getPageNo() > 1 && $this->getPageNo() > $this->countPages()) {
            $this->setPageNo($this->countPages() ?: 1);
        }

        \uasort($objects, $this->getComparator());

        $this->objects = \array_slice(
            $objects,
            ($this->getPageNo() - 1) * $this->getRowsPerPage(),
            $this->getRowsPerPage()
        );
    }

    #[\Override]
    protected function applyFilters(): void
    {
        // This grid view is not backed by a database table, filters would have
        // to be applied in `loadDataSource()`.
    }

    #[\Override]
    protected function getInitializedEvent(): SitemapGridViewInitialized
    {
        return new SitemapGridViewInitialized($this);
    }

    /**
     * @return DatabaseObjectList<DatabaseObject>
     */
    #[\Override]
    protected function createObjectList(): DatabaseObjectList
    {
        return new class extends DatabaseObjectList {};
    }

    /**
     * @return array<string, DatabaseObject>
     */
    private function loadDataSource(): array
    {
        $rows = [];
        foreach (SitemapHandler::getInstance()->getObjects() as $objectName => $object) {
            $rows[$objectName] = $this->createRow($object);
        }

        return $rows;
    }

    private function createRow(RegisteredSitemapObject $object): DatabaseObject
    {
        $handler = SitemapHandler::getInstance();

        return new class(null, [
            'objectName' => $object->getObjectName(),
            'name' => $object->getName(),
            'rebuildTime' => $handler->getRebuildTime($object),
            'isDisabled' => $handler->isDisabled($object) ? 1 : 0,
        ]) extends DatabaseObject implements ILinkableObject {
            protected static $databaseTableIndexName = 'objectName';

            #[\Override]
            public function getLink(): string
            {
                return LinkHandler::getInstance()->getControllerLink(SitemapEditForm::class, [
                    'objectType' => (string)$this->__get('objectName'),
                ]);
            }
        };
    }

    private function getComparator(): \Closure
    {
        $sortField = $this->getSortField() !== '' ? $this->getSortField() : $this->getDefaultSortField();
        $sortOrder = $this->getSortOrder();
        $collator = new \Collator(WCF::getLanguage()->getLocale());

        return static function (DatabaseObject $a, DatabaseObject $b) use ($sortField, $sortOrder, $collator): int {
            $result = (int)$collator->compare((string)$a->__get($sortField), (string)$b->__get($sortField));
            if ($result === 0) {
                // The order must be stable because the rows are paginated.
                $result = \strcmp((string)$a->__get('objectName'), (string)$b->__get('objectName'));
            }

            return $sortOrder === 'ASC' ? $result : -$result;
        };
    }
}
