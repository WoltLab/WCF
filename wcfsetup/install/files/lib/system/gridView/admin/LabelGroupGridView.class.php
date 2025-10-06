<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\LabelGroupEditForm;
use wcf\data\label\group\I18nLabelGroupList;
use wcf\data\label\group\LabelGroup;
use wcf\event\gridView\admin\LabelGroupGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\PhraseColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\interaction\admin\LabelGroupInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\view\filter\I18nTextFilter;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of label groups.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<LabelGroup, I18nLabelGroupList>
 */
final class LabelGroupGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('groupID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('groupName')
                ->label('wcf.global.title')
                ->titleColumn()
                ->renderer(new PhraseColumnRenderer())
                ->filter(I18nTextFilter::class)
                ->sortable(sortByDatabaseColumn: 'groupNameI18n'),
            GridViewColumn::for('groupDescription')
                ->label('wcf.global.description')
                ->filter(TextFilter::class)
                ->unsafeDisableEncoding()
                ->renderer(new TruncatedTextColumnRenderer())
                ->sortable(),
            GridViewColumn::for('labels')
                ->label('wcf.acp.label.list')
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(
                    sortByDatabaseColumn: '(
                        SELECT  COUNT(*)
                        FROM    wcf1_label
                        WHERE   groupID = label_group.groupID
                    )'
                ),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable()
        ]);

        $provider = new LabelGroupInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(LabelGroupEditForm::class)
        ]);
        $this->setInteractionProvider($provider);

        $this->setDefaultSortField('showOrder');
        $this->addRowLink(new GridViewRowLink(LabelGroupEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.content.label.canManageLabel');
    }

    #[\Override]
    protected function createObjectList(): I18nLabelGroupList
    {
        $list = new I18nLabelGroupList();
        if (!empty($list->sqlSelects)) {
            $list->sqlSelects .= ', ';
        }

        $list->sqlSelects .= '(
            SELECT  COUNT(*)
            FROM    wcf1_label
            WHERE   groupID = label_group.groupID
        ) AS labels';

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): LabelGroupGridViewInitialized
    {
        return new LabelGroupGridViewInitialized($this);
    }
}
