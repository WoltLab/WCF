<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\acp\page\ACPSessionLogPage;
use wcf\data\acp\session\log\ACPSessionLogList;
use wcf\data\DatabaseObjectList;
use wcf\event\gridView\admin\ACPSessionLogGridViewInitialized;
use wcf\event\IPsr14Event;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\IpAddressFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\filter\UserFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\IpAddressColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\gridView\renderer\UserLinkColumnRenderer;
use wcf\system\WCF;

/**
 * Grid view for the list of logged admin session.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ACPSessionLogGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('sessionLogID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('userID')
                ->label('wcf.user.username')
                ->sortable(true, 'user_table.username')
                ->titleColumn()
                ->renderer(new UserLinkColumnRenderer(UserEditForm::class))
                ->filter(new UserFilter()),
            GridViewColumn::for('ipAddress')
                ->label('wcf.user.ipAddress')
                ->sortable()
                ->renderer(new IpAddressColumnRenderer())
                ->filter(new IpAddressFilter()),
            GridViewColumn::for('userAgent')
                ->label('wcf.user.userAgent')
                ->sortable()
                ->valueEncoding(false)
                ->renderer(new TruncatedTextColumnRenderer(50))
                ->filter(new TextFilter()),
            GridViewColumn::for('time')
                ->label('wcf.acp.sessionLog.time')
                ->sortable()
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter()),
            GridViewColumn::for('lastActivityTime')
                ->label('wcf.acp.sessionLog.lastActivityTime')
                ->sortable()
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter()),
            GridViewColumn::for('accesses')
                ->label('wcf.acp.sessionLog.actions')
                ->sortable(true, 'accesses')
                ->renderer(new NumberColumnRenderer()),
        ]);

        $this->addRowLink(new GridViewRowLink(ACPSessionLogPage::class));
        $this->setSortField('lastActivityTime');
        $this->setSortOrder('DESC');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.management.canViewLog');
    }

    #[\Override]
    protected function createObjectList(): DatabaseObjectList
    {
        $list = new ACPSessionLogList();
        $list->sqlSelects .= "
            user_table.username,
            0 AS active,
            (
                SELECT  COUNT(*)
                FROM    wcf1_acp_session_access_log
                WHERE   sessionLogID = " . $list->getDatabaseTableAlias() . ".sessionLogID
            ) AS accesses";
        $list->sqlJoins = $list->sqlConditionJoins .= " LEFT JOIN wcf" . WCF_N . "_user user_table ON (user_table.userID = " . $list->getDatabaseTableAlias() . ".userID)";

        return $list;
    }

    #[\Override]
    protected function getInitializedEvent(): ?IPsr14Event
    {
        return new ACPSessionLogGridViewInitialized($this);
    }
}
