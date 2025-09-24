<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\data\DatabaseObject;
use wcf\data\email\log\entry\EmailLogEntry;
use wcf\data\email\log\entry\EmailLogEntryList;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\interaction\IInteraction;
use wcf\system\request\LinkHandler;
use wcf\system\view\filter\SelectFilter;
use wcf\system\view\filter\TextFilter;
use wcf\system\view\filter\TimeFilter;
use wcf\system\view\filter\UserFilter;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of email logs.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<EmailLogEntry, EmailLogEntryList>
 */
final class EmailLogGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('entryID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('subject')
                ->label('wcf.acp.email.log.subject')
                ->titleColumn()
                ->filter(TextFilter::class)
                ->sortable()
                ->valueEncoding(false)
                ->renderer(new TruncatedTextColumnRenderer()),
            GridViewColumn::for('messageID')
                ->label('wcf.acp.email.log.messageId')
                ->filter(TextFilter::class)
                ->sortable()
                ->valueEncoding(false)
                ->renderer(
                    new class(50) extends TruncatedTextColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof EmailLogEntry);

                            return parent::render($row->getFormattedMessageId(), $row);
                        }
                    }
                ),
            GridViewColumn::for('recipient')
                ->label('wcf.user.email')
                ->filter(WCF::getSession()->getPermission("admin.user.canEditMailAddress") ? TextFilter::class : null)
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof EmailLogEntry);

                            if (WCF::getSession()->getPermission("admin.user.canEditMailAddress")) {
                                $recipient = StringUtil::encodeHTML($row->recipient);
                            } else {
                                $recipient = StringUtil::encodeHTML($row->getRedactedRecipientAddress());
                            }

                            return $recipient;
                        }
                    }
                )
                ->sortable(),
            GridViewColumn::for('recipientID')
                ->label('wcf.user.username')
                ->filter(UserFilter::class)
                ->sortable(sortByDatabaseColumn: 'user_table.username')
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof EmailLogEntry);

                            if (!$row->getRecipient()) {
                                return WCF::getLanguage()->get('wcf.user.guest');
                            }

                            $username = StringUtil::encodeHTML($row->getRecipient()->getTitle());

                            if (WCF::getSession()->getPermission('admin.user.canEditUser')) {
                                return \sprintf(
                                    '<a href="%s">%s</a>',
                                    LinkHandler::getInstance()->getControllerLink(UserEditForm::class, [
                                        'id' => $row->getRecipient()->userID,
                                    ]),
                                    $username
                                );
                            } else {
                                return $username;
                            }
                        }
                    }
                ),
            GridViewColumn::for('time')
                ->label('wcf.acp.email.log.time')
                ->renderer(new TimeColumnRenderer())
                ->filter(TimeFilter::class)
                ->sortable(),
            GridViewColumn::for('status')
                ->label('wcf.acp.email.log.status')
                ->filter(new SelectFilter(
                    [
                        EmailLogEntry::STATUS_NEW => 'wcf.acp.email.log.status.new',
                        EmailLogEntry::STATUS_SUCCESS => 'wcf.acp.email.log.status.success',
                        EmailLogEntry::STATUS_TRANSIENT_FAILURE => 'wcf.acp.email.log.status.transient_failure',
                        EmailLogEntry::STATUS_PERMANENT_FAILURE => 'wcf.acp.email.log.status.permanent_failure',
                        EmailLogEntry::STATUS_DISCARDED => 'wcf.acp.email.log.status.discarded',
                    ],
                    'status',
                    'wcf.acp.email.log.status'
                ))
                ->renderer(
                    new class extends DefaultColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof EmailLogEntry);

                            $badgeClasses = match ($row->status) {
                                EmailLogEntry::STATUS_SUCCESS => ' green',
                                EmailLogEntry::STATUS_TRANSIENT_FAILURE => ' yellow',
                                EmailLogEntry::STATUS_PERMANENT_FAILURE => ' red',
                                default => '',
                            };

                            $status = WCF::getLanguage()->get('wcf.acp.email.log.status.' . $row->status);

                            return <<<HTML
                                <span class="badge{$badgeClasses}">{$status}</span>
                                HTML;
                        }
                    }
                )
                ->sortable(),
        ]);

        $this->addQuickInteraction($this->getShowDetailsInteraction());
        $this->setDefaultSortField('time');
        $this->setDefaultSortOrder('DESC');
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.management.canViewLog');
    }

    #[\Override]
    protected function createObjectList(): EmailLogEntryList
    {
        $list = new EmailLogEntryList();
        $join = " LEFT JOIN wcf1_user user_table
                  ON        user_table.userID = email_log_entry.recipientID";
        $list->sqlJoins = $join;

        return $list;
    }

    private function getShowDetailsInteraction(): IInteraction
    {
        return new class('showDetails') extends AbstractInteraction {
            #[\Override]
            public function render(DatabaseObject $object): string
            {
                \assert($object instanceof EmailLogEntry);

                $buttonLabel = WCF::getLanguage()->get('wcf.acp.email.log.button.showDetails');
                $buttonId = 'emailLogDetailsButton' . $object->entryID;
                $id = 'emailLogDetails' . $object->entryID;
                $messageIdLabel = WCF::getLanguage()->get('wcf.acp.email.log.messageId');
                $messageId = StringUtil::encodeHTML($object->messageID);
                $messageLabel = WCF::getLanguage()->get('wcf.acp.email.log.statusMessage');
                $message = StringUtil::encodeHTML($object->message);
                $dialogTitle = StringUtil::encodeJS(WCF::getLanguage()->get('wcf.acp.email.log.details'));

                return <<<HTML
                    <button type="button" id="{$buttonId}" class="jsTooltip" title="{$buttonLabel}">
                        <fa-icon name="magnifying-glass"></fa-icon>
                    </button>
                    <template id="{$id}">
                        <dl>
                            <dt>{$messageIdLabel}</dt>
                            <dd>{$messageId}</dd>
                            <dt>{$messageLabel}</dt>
                            <dd>{$message}</dd>
                        </dl>
                    </template>
                    <script data-relocate="true">
                        require(['WoltLabSuite/Core/Component/Dialog'], ({ dialogFactory }) => {
                            document.getElementById('{$buttonId}').addEventListener('click', () => {
                                const dialog = dialogFactory().fromId('{$id}').withoutControls();
                                dialog.show('{$dialogTitle}');
                            });
                        });
                    </script>
                    HTML;
            }
        };
    }
}
