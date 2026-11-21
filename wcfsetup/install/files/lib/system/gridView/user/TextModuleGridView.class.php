<?php

namespace wcf\system\gridView\user;

use wcf\data\DatabaseObject;
use wcf\data\text\module\TextModule;
use wcf\data\text\module\TextModuleList;
use wcf\event\gridView\user\TextModuleGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\IInteraction;
use wcf\system\interaction\user\TextModuleInteractions;
use wcf\system\language\LanguageFactory;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\view\ViewPrimaryButton;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of text modules shown below the editor.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractGridView<TextModule, TextModuleList>
 */
final class TextModuleGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumn(
            GridViewColumn::for('title')
                ->label('wcf.global.title')
                ->titleColumn()
                ->filter(TextFilter::class)
                ->sortable()
        );

        if (LanguageFactory::getInstance()->multilingualismEnabled()) {
            $this->addColumn(
                GridViewColumn::for('languageID')
                    ->label('wcf.user.language')
                    ->filter(new SelectFilter(
                        LanguageFactory::getInstance()->getContentLanguages(),
                        'languageID',
                        'wcf.user.language'
                    ))
                    ->renderer(
                        new class extends AbstractColumnRenderer {
                            #[\Override]
                            public function render(mixed $value, DatabaseObject $row): string
                            {
                                \assert($row instanceof TextModule);

                                $language = $row->getLanguage();
                                if ($language === null) {
                                    return WCF::getLanguage()->get('wcf.textModule.language.all');
                                }

                                return \sprintf(
                                    '<img src="%s" alt="" class="iconFlag"> %s',
                                    StringUtil::encodeHTML($language->getIconPath()),
                                    StringUtil::encodeHTML($language->languageName)
                                );
                            }
                        }
                    )
            );
        }

        $this->addColumn(
            GridViewColumn::for('userID')
                ->label('wcf.user.username')
                ->renderer(new UserLinkColumnRenderer())
                ->filter(UserFilter::class)
                ->sortable(sortByDatabaseColumn: 'text_module.username')
        );

        $this->addColumn(
            GridViewColumn::for('message')
                ->label('wcf.textModule.message')
                ->unsafeDisableEncoding()
                ->renderer(
                    new class extends TruncatedTextColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof TextModule);

                            return parent::render($row->getPlainTextMessage(), $row);
                        }
                    }
                )
        );

        $this->setInteractionProvider(new TextModuleInteractions());
        $this->addQuickInteraction($this->getInsertInteraction());
        $this->setDefaultSortField('title');
        $this->setRowsPerPage(10);
        $this->setPrimaryButton(ViewPrimaryButton::forButton(
            'wcf.textModule.add',
            FontAwesomeIcon::fromValues('plus'),
            'textModuleList__addButton',
        ));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return TextModule::canUse();
    }

    #[\Override]
    protected function createObjectList(): TextModuleList
    {
        return new TextModuleList();
    }

    #[\Override]
    protected function getInitializedEvent(): TextModuleGridViewInitialized
    {
        return new TextModuleGridViewInitialized($this);
    }

    /**
     * The click is handled by the editor's text module tab, which knows the
     * target editor.
     */
    private function getInsertInteraction(): IInteraction
    {
        return new class('insert') extends AbstractInteraction {
            #[\Override]
            public function render(DatabaseObject $object): string
            {
                \assert($object instanceof TextModule);

                $title = StringUtil::encodeHTML(WCF::getLanguage()->get('wcf.global.button.insert'));

                return <<<HTML
                    <button type="button" class="button buttonPrimary small jsTooltip" title="{$title}" data-text-module-insert="{$object->textModuleID}">
                        <fa-icon name="paste"></fa-icon>
                    </button>
                    HTML;
            }
        };
    }
}
