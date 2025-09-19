<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\PageEditForm;
use wcf\data\application\Application;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\event\gridView\admin\PageGridViewInitialized;
use wcf\system\application\ApplicationHandler;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\BooleanFilter;
use wcf\system\gridView\filter\ObjectIdFilter;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\admin\PageInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of pages.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<Page, PageList>
 */
final class PageGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('pageID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(new ObjectIdFilter())
                ->sortable(),
            GridViewColumn::for('name')
                ->label('wcf.global.name')
                ->titleColumn()
                ->renderer(new DefaultColumnRenderer())
                ->filter(new TextFilter())
                ->sortable(),
            GridViewColumn::for('pageTitle')
                ->label('wcf.global.title')
                ->filter($this->getPageContentFilter('title'))
                ->hidden(),
            GridViewColumn::for('pageContent')
                ->label('wcf.acp.page.content')
                ->filter($this->getPageContentFilter('content'))
                ->hidden(),
            GridViewColumn::for('url')
                ->label('wcf.acp.page.url')
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Page);

                            return $row->getDisplayLink();
                        }
                    }
                ),
            GridViewColumn::for('pageType')
                ->label('wcf.acp.page.type')
                ->renderer(new DefaultColumnRenderer())
                ->filter(
                    new SelectFilter([
                        'text' => 'wcf.acp.page.type.text',
                        'html' => 'wcf.acp.page.type.html',
                        'tpl' => 'wcf.acp.page.type.tpl',
                        'system' => 'wcf.acp.page.type.system',
                    ])
                )
                ->sortable(),
            GridViewColumn::for('applicationPackageID')
                ->label('wcf.acp.page.application')
                ->filter($this->getApplicationFilter())
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Page);

                            $application = $row->getApplication();

                            return StringUtil::encodeHTML($application->domainName . $application->domainPath);
                        }
                    }
                )
                ->hidden(),
            GridViewColumn::for('lastUpdateTime')
                ->label('wcf.acp.page.lastUpdateTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter())
                ->sortable(),
            GridViewColumn::for('originIsSystem')
                ->label('wcf.acp.page.originIsNotSystem')
                ->filter(new BooleanFilter(reverseValue: true))
                ->hidden(),
            GridViewColumn::for('controllerCustomURL')
                ->label('wcf.acp.page.customURL')
                ->filter(
                    new class extends BooleanFilter {
                        #[\Override]
                        public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
                        {
                            $list->getConditionBuilder()->add(
                                "(page.controllerCustomURL <> ? OR page.pageType <> ?)",
                                ['', 'system']
                            );
                        }
                    }
                )
                ->hidden(),
        ]);

        $provider = new PageInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(PageEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/pages/%s/enable',
                'core/pages/%s/disable',
                isAvailableCallback: static fn(Page $page): bool => $page->canDisable()
            )
        );

        $this->setDefaultSortField('name');
        $this->addRowLink(new GridViewRowLink(PageEditForm::class));
    }

    private function getPageContentFilter(string $databaseColumn): TextFilter
    {
        return new class($databaseColumn) extends TextFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
            {
                $list->getConditionBuilder()->add(
                    "page.pageID IN (
                    SELECT  pageID
                    FROM    wcf1_page_content
                    WHERE   {$this->databaseColumn} LIKE ?
                )",
                    ['%' . $value . '%']
                );
            }
        };
    }

    private function getApplicationFilter(): SelectFilter
    {
        $applications = \array_map(static function (Application $application): string {
            return $application->domainName . $application->domainPath;
        }, ApplicationHandler::getInstance()->getApplications());

        return new class($applications) extends SelectFilter {
            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $id, string $value): void
            {
                $list->getConditionBuilder()->add(
                    '((page.applicationPackageID = ? AND page.overrideApplicationPackageID IS NULL) OR page.overrideApplicationPackageID = ?)',
                    [
                        $value,
                        $value,
                    ]
                );
            }
        };
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.content.cms.canManagePage');
    }

    #[\Override]
    protected function createObjectList(): PageList
    {
        return new PageList();
    }

    #[\Override]
    protected function getInitializedEvent(): PageGridViewInitialized
    {
        return new PageGridViewInitialized($this);
    }
}
