<?php

namespace wcf\system\importer;

use wcf\data\article\Article;
use wcf\data\article\content\ArticleContentEditor;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Imports article attachments.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ArticleAttachmentImporter extends AbstractAttachmentImporter
{
    public function __construct()
    {
        $objectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', 'com.woltlab.wcf.article.content');
        \assert($objectType !== null);
        $this->objectTypeID = $objectType->objectTypeID;
    }

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        $articleID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.article', $data['objectID']);
        if ($articleID === null) {
            return 0;
        }

        $article = new Article($articleID);
        $articleContents = $article->getArticleContents();
        $firstContent = \reset($articleContents);
        if ($firstContent === false) {
            return 0;
        }

        $data['objectID'] = $firstContent->getObjectID();

        $attachmentID = parent::import($oldID, $data, $additionalData);
        // @phpstan-ignore notEqual.notAllowed (the old id originates from the import data and can be a numeric string)
        if ($attachmentID && $attachmentID != $oldID) {
            foreach ($articleContents as $content) {
                $newMessage = $this->fixEmbeddedAttachments(
                    $content->content,
                    $oldID,
                    $attachmentID
                );

                if ($newMessage) {
                    (new ArticleContentEditor($content))->update([
                        'content' => $newMessage,
                    ]);
                }
            }
        }

        return $attachmentID;
    }
}
