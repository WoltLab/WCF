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
    #[\Override]
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

    #[\Override]
    public function loadObjects(array $objectIDs)
    {
        $viewableMedia = ViewableMediaRuntimeCache::getInstance()->getObjects($objectIDs);
        $contentLanguageID = MessageEmbeddedObjectManager::getInstance()->getContentLanguageID();
        if ($contentLanguageID !== null) {
            $mediaIDs = [];
            foreach ($viewableMedia as $media) {
                // @phpstan-ignore property.notFound
                if ($media !== null && (int)$media->localizedLanguageID !== $contentLanguageID) {
                    $mediaIDs[] = $media->getDecoratedObject()->mediaID;
                }
            }

            if ($mediaIDs !== []) {
                $conditions = new PreparedStatementConditionBuilder();
                $conditions->add("mediaID IN (?)", [$mediaIDs]);
                $conditions->add("languageID = ?", [$contentLanguageID]);

                $sql = "SELECT  *
                        FROM    wcf1_media_content
                        " . $conditions;
                $statement = WCF::getDB()->prepare($sql);
                $statement->execute($conditions->getParameters());
                while ($row = $statement->fetchArray()) {
                    $viewableMedia[$row['mediaID']]->setLocalizedContent($row['languageID'], $row);
                }
            }
        }

        return $viewableMedia;
    }

    #[\Override]
    public function validateValues(string $objectType, int $objectID, array $values)
    {
        $mediaList = new MediaList();
        $mediaList->getConditionBuilder()->add("media.mediaID IN (?)", [$values]);
        $mediaList->readObjectIDs();

        return $mediaList->getObjectIDs();
    }

    #[\Override]
    public function replaceSimple(string $objectType, int $objectID, string|int $value, array $attributes)
    {
        /** @var ?Media $media */
        $media = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.media', $value);
        if ($media === null) {
            return null;
        }

        $return = (!empty($attributes['return'])) ? $attributes['return'] : 'link';
        switch ($return) {
            case 'title':
                return $media->getTitle();

            case 'link':
            default:
                $size = (!empty($attributes['size'])) ? $attributes['size'] : 'original';
                switch ($size) {
                    case 'small':
                    case 'medium':
                    case 'large':
                        return $media->getThumbnailLink($size);

                    case 'original':
                    default:
                        return $media->getLink();
                }
        }
    }
}
