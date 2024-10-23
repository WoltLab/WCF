<?php

namespace wcf\system\message\embedded\object;

use wcf\data\media\Media;
use wcf\data\media\MediaList;
use wcf\system\cache\runtime\ViewableMediaRuntimeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * IMessageEmbeddedObjectHandler implementation for shared media.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MediaMessageEmbeddedObjectHandler extends AbstractSimpleMessageEmbeddedObjectHandler
{
    /**
     * @inheritDoc
     */
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        $mediaIDs = [];
        foreach (['wsm', 'wsmg'] as $name) {
            if (empty($embeddedData[$name])) {
                continue;
            }

            for ($i = 0, $length = \count($embeddedData[$name]); $i < $length; $i++) {
                $parsedIDs = ArrayUtil::toIntegerArray(\explode(',', $embeddedData[$name][$i][0]));

                $mediaIDs = \array_merge($mediaIDs, $parsedIDs);
            }
        }

        return \array_unique($mediaIDs);
    }

    /**
     * @inheritDoc
     */
    public function loadObjects(array $objectIDs)
    {
        $viewableMedia = ViewableMediaRuntimeCache::getInstance()->getObjects($objectIDs);
        $contentLanguageID = MessageEmbeddedObjectManager::getInstance()->getContentLanguageID();
        if ($contentLanguageID !== null) {
            $mediaIDs = [];
            foreach ($viewableMedia as $media) {
                if ($media !== null && $media->localizedLanguageID != $contentLanguageID) {
                    $mediaIDs[] = $media->getDecoratedObject()->mediaID;
                }
            }

            if (!empty($mediaIDs)) {
                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add("mediaID IN (?)", [$mediaIDs]);
                $conditions->add("languageID = ?", [$contentLanguageID]);

                $sql = "SELECT  *
                        FROM    wcf" . WCF_N . "_media_content
                        " . $conditions;
                $statement = WCF::getDB()->prepareStatement($sql);
                $statement->execute($conditions->getParameters());
                while ($row = $statement->fetchArray()) {
                    $viewableMedia[$row['mediaID']]->setLocalizedContent($row['languageID'], $row);
                }
            }
        }

        return $viewableMedia;
    }

    /**
     * @inheritDoc
     */
    public function validateValues($objectType, $objectID, array $values)
    {
        $mediaList = new MediaList();
        $mediaList->getConditionBuilder()->add("media.mediaID IN (?)", [$values]);
        $mediaList->readObjectIDs();

        return $mediaList->getObjectIDs();
    }

    /**
     * @inheritDoc
     */
    public function replaceSimple($objectType, $objectID, $value, array $attributes)
    {
        /** @var Media $media */
        $media = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.media', $value);
        if ($media === null) {
            return;
        }

        $return = (!empty($attributes['return'])) ? $attributes['return'] : 'link';
        switch ($return) {
            case 'title':
                return $media->getTitle();
                break;

            case 'link':
            default:
                $size = (!empty($attributes['size'])) ? $attributes['size'] : 'original';
                switch ($size) {
                    case 'small':
                    case 'medium':
                    case 'large':
                        return $media->getThumbnailLink($size);
                        break;

                    case 'original':
                    default:
                        return $media->getLink();
                        break;
                }

                break;
        }
    }
}
