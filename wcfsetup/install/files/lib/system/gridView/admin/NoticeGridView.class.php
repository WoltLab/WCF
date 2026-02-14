<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\NoticeEditForm;
use wcf\data\notice\Notice;
use wcf\data\notice\NoticeList;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\interaction\admin\NoticeInteractions;
use wcf\system\interaction\bulk\admin\NoticeBulkInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\interaction\ToggleInteraction;
use wcf\system\view\filter\IntegerFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\WCF;

/**
 * Grid view for the list of notices.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.2
 *
 * @extends AbstractGridView<Notice, NoticeList>
 */
final class NoticeGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('noticeID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('noticeName')
                ->label('wcf.global.name')
                ->titleColumn()
                ->filter(TextFilter::class)
                ->sortable(),
            GridViewColumn::for('showOrder')
                ->label('wcf.global.showOrder')
                ->renderer(new NumberColumnRenderer())
                ->filter(IntegerFilter::class)
                ->sortable(),
        ]);

        $provider = new NoticeInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(NoticeEditForm::class)
        ]);
        $this->setInteractionProvider($provider);
        $this->setBulkInteractionProvider(new NoticeBulkInteractions());

        $this->addQuickInteraction(
            new ToggleInteraction(
                'enable',
                'core/notices/%s/enable',
                'core/notices/%s/disable'
            )
        );

        $this->setDefaultSortField("showOrder");
        $this->addRowLink(new GridViewRowLink(NoticeEditForm::class));
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission("admin.notice.canManageNotice");
    }

    #[\Override]
    protected function createObjectList(): NoticeList
    {
        return new NoticeList();
    }
}
