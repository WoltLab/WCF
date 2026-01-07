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
            ->getObjectTypeByName('com.woltlab.wcf.attachment.objectType', 'com.woltlab.wcf.article');
        \assert($objectType !== null);
        $this->objectTypeID = $objectType->objectTypeID;
    }

    #[\Override]
    public function import($oldID, array $data, array $additionalData = [])
    {
        $data['objectID'] = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.article', $data['objectID']);
        if (!$data['objectID']) {
            return 0;
        }

        $attachmentID = parent::import($oldID, $data, $additionalData);
        if ($attachmentID && $attachmentID != $oldID) {
            $article = new Article($data['objectID']);

            foreach ($article->getArticleContents() as $content) {
                $newMessage = $this->fixEmbeddedAttachments(
                    $content->message,
                    $oldID,
                    $attachmentID
                );

                if ($newMessage) {
                    (new ArticleContentEditor($content))->update([
                        'message' => $newMessage,
                    ]);
                }
            }
        }

        return $attachmentID;
    }
}
