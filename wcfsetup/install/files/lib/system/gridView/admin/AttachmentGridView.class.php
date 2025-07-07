<?php

namespace wcf\system\gridView\admin;

use wcf\acp\form\UserEditForm;
use wcf\data\attachment\AdministrativeAttachment;
use wcf\data\attachment\AdministrativeAttachmentList;
use wcf\data\DatabaseObject;
use wcf\event\gridView\admin\AttachmentGridViewInitialized;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\NumericFilter;
use wcf\system\gridView\filter\ObjectIdFilter;
use wcf\system\gridView\filter\SelectFilter;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\filter\UserFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\GridViewRowLink;
use wcf\system\gridView\renderer\AbstractColumnRenderer;
use wcf\system\gridView\renderer\FilesizeColumnRenderer;
use wcf\system\gridView\renderer\ILinkColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TruncatedTextColumnRenderer;
use wcf\system\interaction\admin\AttachmentInteractions;
use wcf\system\interaction\bulk\admin\AttachmentBulkInteractions;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Grid view for the list of attachments.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractGridView<AdministrativeAttachment, AdministrativeAttachmentList>
 */
final class AttachmentGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('fileID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->filter(new ObjectIdFilter())
                ->sortable(),

            GridViewColumn::for('preview')
                ->label('wcf.attachment.preview')
                ->renderer(
                    new class extends AbstractColumnRenderer implements ILinkColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof AdministrativeAttachment);

                            $isImage = $row->isImage;
                            $link = StringUtil::encodeHTML($row->getLink());
                            $fancyBox = $isImage ? ' data-type="image" data-fancybox="attachments" data-caption="' . StringUtil::encodeHTML($row->filename) . '"' : '';
                            if ($row->tinyThumbnailType) {
                                $thumbnailLink = \sprintf(
                                    '<img src="%s" class="attachmentTinyThumbnail" alt="">',
                                    StringUtil::encodeHTML($row->getThumbnailLink('tiny'))
                                );
                            } else {
                                $thumbnailLink = FontAwesomeIcon::fromValues($row->getIconName())->toHtml(64);
                            }

                            return <<<HTML
                                <a href="{$link}"{$fancyBox}>
                                    {$thumbnailLink}
                                </a>
                                HTML;
                        }

                        #[\Override]
                        public function getClasses(): string
                        {
                            return 'gridView__column--image';
                        }
                    }
                ),
            GridViewColumn::for('filename')
                ->label('wcf.attachment.filename')
                ->titleColumn()
                ->filter(new TextFilter('file_table.filename'))
                ->renderer(new TruncatedTextColumnRenderer())
                ->sortable(sortByDatabaseColumn: 'file_table.filename'),
            GridViewColumn::for('fileType')
                ->label('wcf.attachment.fileType')
                ->filter(new SelectFilter($this->getAvailableFileTypes(), 'file_table.mimeType'))
                ->hidden(),
            GridViewColumn::for('username')
                ->label('wcf.user.username')
                ->filter(new UserFilter('user_table.username'))
                ->renderer(
                    new class extends AbstractColumnRenderer {
                        #[\Override]
                        public function render(mixed $value, DatabaseObject $row): string
                        {
                            \assert($row instanceof AdministrativeAttachment);

                            if (!$row->userID) {
                                return WCF::getLanguage()->get('wcf.user.guest');
                            }

                            if (WCF::getSession()->getPermission('admin.user.canEditUser')) {
                                return \sprintf(
                                    '<a href="%s">%s</a>',
                                    LinkHandler::getInstance()->getControllerLink(UserEditForm::class, [
                                        'id' => $row->userID,
                                    ]),
                                    $row->username
                                );
                            } else {
                                return $row->username;
                            }
                        }
                    }
                )
                ->sortable(sortByDatabaseColumn: 'user_table.username'),
            GridViewColumn::for('uploadTime')
                ->label('wcf.attachment.uploadTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter())
                ->sortable(),
            GridViewColumn::for('downloads')
                ->label('wcf.attachment.downloads')
                ->filter(new NumericFilter())
                ->renderer(new NumberColumnRenderer())
                ->sortable(),
            GridViewColumn::for('filesize')
                ->label('wcf.attachment.filesize')
                ->renderer(new FilesizeColumnRenderer())
                ->sortable(sortByDatabaseColumn: 'file_table.filesize'),
            GridViewColumn::for('lastDownloadTime')
                ->label('wcf.attachment.lastDownloadTime')
                ->renderer(new TimeColumnRenderer())
                ->filter(new TimeFilter())
                ->sortable(),
        ]);

        $interaction = new AttachmentInteractions();
        $this->setInteractionProvider($interaction);
        $this->setBulkInteractionProvider(new AttachmentBulkInteractions());
        $this->addRowLink(new GridViewRowLink(isLinkableObject: true));

        $this->setSortOrder('DESC');
        $this->setSortField('uploadTime');
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableFileTypes(): array
    {
        $sql = "SELECT    DISTINCT file_table.mimeType
                FROM      wcf1_attachment attachment
                LEFT JOIN wcf1_file file_table
                ON        (file_table.fileID = attachment.fileID)
                WHERE     attachment.fileID IS NOT NULL";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        $fileTypes = $statement->fetchAll(\PDO::FETCH_COLUMN);

        \ksort($fileTypes);

        return \array_combine($fileTypes, $fileTypes);
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.attachment.canManageAttachment');
    }

    #[\Override]
    protected function createObjectList(): AdministrativeAttachmentList
    {
        return new AdministrativeAttachmentList();
    }

    #[\Override]
    protected function getInitializedEvent(): AttachmentGridViewInitialized
    {
        return new AttachmentGridViewInitialized($this);
    }
}
