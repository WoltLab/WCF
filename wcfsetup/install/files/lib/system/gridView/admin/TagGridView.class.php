<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\TagEditForm;
use wcf\data\DatabaseObject;
use wcf\data\tag\Tag;
use wcf\data\tag\TagList;
use wcf\event\gridView\admin\TagGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\TagInteractions;
use wcf\system\interaction\bulk\admin\TagBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\language\LanguageFactory;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\ObjectIdFilter;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of tags.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<Tag, TagList>
 */
final class TagGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('tagID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable()
                ->filter(ObjectIdFilter::class),
            GridViewColumn::for('name')
                ->label('wcf.acp.tag.name')
                ->titleColumn()
                ->filter(TextFilter::class)
                ->sortable(),
            GridViewColumn::for('synonymName')
                ->label('wcf.acp.tag.synonymFor')
                ->renderer(new DefaultColumnRenderer())
                ->filter(new TextFilter('synonymName', 'wcf.acp.tag.synonymFor', 'synonym.name'))
                ->sortable(sortByDatabaseColumn: "synonym.name"),
            GridViewColumn::for('languageName')
                ->label('wcf.acp.tag.languageID')
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof Tag);

                            if (!$row->languageID) {
                                return '';
                            }

                            $language = LanguageFactory::getInstance()->getLanguage($row->languageID);
                            if ($language === null) {
                                return '';
                            }

                            return \sprintf(
                                '%s (%s)',
                                StringUtil::encodeHTML($language->languageName),
                                StringUtil::encodeHTML($language->languageCode),
                            );
                        }
                    }
                )
                ->filter(new SelectFilter(
                    LanguageFactory::getInstance()->getLanguages(),
                    'languageName',
                    'wcf.acp.tag.languageID',
                    'tag.languageID'
                ))
                ->sortable(sortByDatabaseColumn: 'language.languageName'),
            GridViewColumn::for('usageCount')
                ->label('wcf.acp.tag.usageCount')
                ->renderer(new NumberColumnRenderer())
                ->filter(new IntegerFilter('usageCount', 'wcf.acp.tag.usageCount', $this->subSelectUsageCount()))
                ->sortable(sortByDatabaseColumn: $this->subSelectUsageCount()),
        ]);

        $provider = new TagInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(TagEditForm::class)
        ]);
        $this->setBulkInteractionProvider(new TagBulkInteractions());
        $this->setInteractionProvider($provider);

        $this->setDefaultSortField('name');
        $this->addRowLink(new GridViewRowLink(TagEditForm::class));
    }

    private function subSelectUsageCount(): string
    {
        return "(
            SELECT  COUNT(*)
            FROM    wcf1_tag_to_object t2o
            WHERE   t2o.tagID = tag.tagID
        )";
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return \MODULE_TAGGING
            && WCF::getSession()->getPermission('admin.content.tag.canManageTag');
    }

    #[\Override]
    protected function createObjectList(): TagList
    {
        $list = new TagList();
        $list->sqlSelects = $this->subSelectUsageCount() . ' AS usageCount';
        $list->sqlSelects .= ", language.languageName, language.languageCode";
        $list->sqlSelects .= ", synonym.name AS synonymName";

        $list->sqlJoins = "
            LEFT JOIN   wcf1_language language
            ON          tag.languageID = language.languageID
            LEFT JOIN   wcf1_tag synonym
            ON          tag.synonymFor = synonym.tagID";
        $list->sqlConditionJoins = "
            LEFT JOIN   wcf1_tag synonym
            ON          tag.synonymFor = synonym.tagID";

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): TagGridViewInitialized
    {
        return new TagGridViewInitialized($this);
    }
}
