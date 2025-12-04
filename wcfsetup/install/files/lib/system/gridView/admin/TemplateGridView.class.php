<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\TemplateAddForm;
use wcf\acp\form\TemplateEditForm;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\template\group\TemplateGroup;
use wcf\data\template\group\TemplateGroupNode;
use wcf\data\template\group\TemplateGroupNodeTree;
use wcf\data\template\Template;
use wcf\data\template\TemplateList;
use wcf\event\gridView\admin\TemplateGridViewInitialized;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\TemplateGroupCacheBuilder;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\admin\TemplateInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\view\filter\AbstractFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of templates.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<Template, TemplateList>
 */
final class TemplateGridView extends AbstractGridView
{
    public const DEFAULT_TEMPLATE_GROUP_ID = -1;

    public function __construct(?int $templateGroupID = null)
    {
        $this->addColumns([
            GridViewColumn::for("templateID")
                ->label("wcf.global.objectID")
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for("application")
                ->label("wcf.acp.template.application")
                ->filter(new SelectFilter(
                    $this->getApplications(),
                    'application',
                    'wcf.acp.template.application'
                ))
                ->renderer(new DefaultColumnRenderer())
                ->sortable(),
            GridViewColumn::for("templateGroupID")
                ->label("wcf.acp.template.group")
                ->filter($this->getTemplateGroupFilter())
                ->hidden(),
            GridViewColumn::for("templateName")
                ->label("wcf.global.name")
                ->titleColumn()
                ->sortable()
                ->filter(TextFilter::class),
            GridViewColumn::for("lastModificationTime")
                ->label("wcf.acp.template.lastModificationTime")
                ->filter(TimeFilter::class)
                ->renderer(new TimeColumnRenderer())
                ->sortable(),
        ]);

        $provider = new TemplateInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(
                TemplateEditForm::class,
                static fn(Template $template) => $template->templateGroupID !== null
            ),
        ]);
        $this->setInteractionProvider($provider);
        $this->addRowLink(new class extends GridViewRowLink {
            #[\Override]
            public function render(mixed $value, DatabaseObject $row, bool $isPrimaryColumn = false): string
            {
                \assert($row instanceof Template);

                if ($row->templateGroupID) {
                    $link = LinkHandler::getInstance()->getControllerLink(TemplateEditForm::class, ['object' => $row]);
                } else {
                    $link = LinkHandler::getInstance()->getControllerLink(TemplateAddForm::class, ['copy' => $row->templateID]);
                }

                return \sprintf(
                    '<a href="%s" class="gridView__rowLink" tabindex="%s">%s</a>',
                    $link,
                    $isPrimaryColumn ? '0' : '-1',
                    $value,
                );
            }
        });

        $this->setDefaultSortField("templateName");

        if ($templateGroupID !== null) {
            $this->setActiveFilters([
                "templateGroupID" => $templateGroupID
            ]);
        }
    }

    /**
     * @return array<string, Package>
     */
    private function getApplications(): array
    {
        $applications = ApplicationHandler::getInstance()->getApplications();
        $applications[] = ApplicationHandler::getInstance()->getWCF();
        $availableApplications = [];

        foreach ($applications as $application) {
            $package = PackageCache::getInstance()->getPackage($application->packageID);
            $availableApplications[ApplicationHandler::getInstance()->getAbbreviation($package->packageID)] = $package;
            $package->getName();
        }

        $collator = new \Collator(WCF::getLanguage()->getLocale());
        \uasort(
            $availableApplications,
            static fn(Package $a, Package $b) => $collator->compare($a->getName(), $b->getName())
        );

        return $availableApplications;
    }

    private function getTemplateGroupFilter(): AbstractFilter
    {
        return new class('templateGroupID', 'wcf.acp.template.group') extends AbstractFilter {
            #[\Override]
            public function getFormField(): AbstractFormField
            {
                return SingleSelectionFormField::create($this->id)
                    ->label($this->languageItem)
                    ->nullable()
                    ->options($this->getSelectOptions(), true);
            }

            #[\Override]
            public function applyFilter(DatabaseObjectList $list, string $value): void
            {
                $columnName = $this->getDatabaseColumnName($list);

                if ($value == TemplateGridView::DEFAULT_TEMPLATE_GROUP_ID) {
                    $list->getConditionBuilder()->add("{$columnName} IS NULL");
                } else {
                    $list->getConditionBuilder()->add("{$columnName} = ?", [$value]);
                }
            }

            #[\Override]
            public function renderValue(string $value): string
            {
                if ($value == TemplateGridView::DEFAULT_TEMPLATE_GROUP_ID) {
                    return WCF::getLanguage()->get('wcf.acp.template.group.default');
                }

                $templateGroup = TemplateGroupCacheBuilder::getInstance()->getData()[$value];
                \assert($templateGroup instanceof TemplateGroup);

                return $templateGroup->getTitle();
            }

            /**
             * @return list<array{depth: int, value: int, label: string}>
             */
            private function getSelectOptions(): array
            {
                $options = [
                    [
                        'depth' => 0,
                        'value' => TemplateGridView::DEFAULT_TEMPLATE_GROUP_ID,
                        'label' => 'wcf.acp.template.group.default'
                    ]
                ];

                foreach ((new TemplateGroupNodeTree())->getIterator() as $node) {
                    \assert($node instanceof TemplateGroupNode);
                    $options[] = [
                        'depth' => $node->getDepth(),
                        'value' => $node->templateGroupID,
                        'label' => $node->getTitle()
                    ];
                }

                return $options;
            }
        };
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.template.canManageTemplate');
    }

    #[\Override]
    protected function createObjectList(): TemplateList
    {
        return new TemplateList();
    }

    #[\Override]
    protected function getInitializedEvent(): TemplateGridViewInitialized
    {
        return new TemplateGridViewInitialized($this);
    }
}
