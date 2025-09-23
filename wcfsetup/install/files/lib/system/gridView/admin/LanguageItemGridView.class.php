<?php

namespace wcf\system\gridView\admin;

use wcf\acp\action\LanguageItemEditAction;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\item\LanguageItem;
use wcf\data\language\item\LanguageItemList;
use wcf\data\language\Language;
use wcf\data\language\LanguageList;
use wcf\event\gridView\admin\LanguageItemGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\interaction\admin\LanguageItemInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\FormBuilderDialogInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\view\filter\BooleanFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of language items.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<LanguageItem, LanguageItemList>
 */
final class LanguageItemGridView extends AbstractGridView
{
    public function __construct(?Language $defaultLanguage = null)
    {
        $availableLanguages = $this->getAvailableLanguages();

        $this->addColumns([
            GridViewColumn::for('languageItem')
                ->label('wcf.global.name')
                ->titleColumn()
                ->filter(TextFilter::class)
                ->sortable(),
            GridViewColumn::for('languageID')
                ->label('wcf.user.language')
                ->filter(new SelectFilter($this->getAvailableLanguages(), 'languageID', 'wcf.user.language'))
                ->renderer(
                    new class($availableLanguages) extends AbstractColumnRenderer {
                        /**
                         * @param array<int, string> $availableLanguages
                         */
                        public function __construct(private readonly array $availableLanguages) {}

                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            return $this->availableLanguages[$value];
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for('languageItemValue')
                ->label('wcf.acp.language.item.value')
                ->valueEncoding(false)
                ->renderer(new TruncatedTextColumnRenderer(255))
                ->filter(TextFilter::class)
                ->sortable(),
            GridViewColumn::for('languageCustomItemValue')
                ->label('wcf.acp.language.item.customValue')
                ->valueEncoding(false)
                ->renderer(
                    new class(255) extends TruncatedTextColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof LanguageItem);

                            if ($row->languageUseCustomValue) {
                                return parent::render($value, $row);
                            }

                            return '<s>' . parent::render($value, $row) . '</s>';
                        }
                    }
                )
                ->filter(TextFilter::class)
                ->sortable(),
        ]);

        $this->addAvailableFilters([
            new SelectFilter(
                $this->getAvailableCategories(),
                'languageCategoryID',
                'wcf.global.category',
                labelLanguageItems: false
            ),
            new class('languageUseCustomValue', 'wcf.acp.language.item.customValues') extends BooleanFilter {
                #[\Override]
                public function applyFilter(DatabaseObjectList $list, string $value): void
                {
                    $list->getConditionBuilder()->add("languageCustomItemValue IS NOT NULL");
                }
            },
            new class('hasDisabledCustomValue', 'wcf.acp.language.item.disabledCustomValues') extends BooleanFilter {
                #[\Override]
                public function applyFilter(DatabaseObjectList $list, string $value): void
                {
                    $list->getConditionBuilder()->add("languageCustomItemValue IS NOT NULL");
                    $list->getConditionBuilder()->add("languageUseCustomValue = ?", [0]);
                }
            },
            new class('hasRecentlyDisabledCustomValue', 'wcf.acp.language.item.recentlyDisabledCustomValues') extends BooleanFilter {
                #[\Override]
                public function applyFilter(DatabaseObjectList $list, string $value): void
                {
                    $list->getConditionBuilder()->add("languageCustomItemValue IS NOT NULL");
                    $list->getConditionBuilder()->add(
                        "languageCustomItemDisableTime >= ?",
                        [TIME_NOW - 86400 * 7]
                    );
                }
            },
            new BooleanFilter('isCustomLanguageItem', 'wcf.acp.language.item.isCustomLanguageItem'),
        ]);
        $provider = new LanguageItemInteractions();
        $provider->addInteractions([
            new Divider(),
            new FormBuilderDialogInteraction(
                'edit',
                LinkHandler::getInstance()->getControllerLink(
                    LanguageItemEditAction::class,
                    ['id' => '%s']
                ),
                'wcf.global.button.edit'
            )
        ]);
        $this->setInteractionProvider($provider);
        $this->setDefaultSortField('languageItem');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.language.canManageLanguage');
    }

    #[\Override]
    protected function createObjectList(): LanguageItemList
    {
        return new LanguageItemList();
    }

    #[\Override]
    protected function getInitializedEvent(): LanguageItemGridViewInitialized
    {
        return new LanguageItemGridViewInitialized($this);
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableCategories(): array
    {
        $list = new LanguageCategoryList();
        $list->readObjects();

        return \array_map(static fn($object) => $object->languageCategory, $list->getObjects());
    }

    /**
     * @return array<int, string>
     */
    private function getAvailableLanguages(): array
    {
        $list = new LanguageList();
        $list->readObjects();

        return $list->getObjects();
    }
}
