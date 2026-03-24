<?php

namespace wcf\system\message\embedded\object;

use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\util\ArrayUtil;

/**
 * Parses embedded pages and outputs their link or title.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PageMessageEmbeddedObjectHandler extends AbstractSimpleMessageEmbeddedObjectHandler
{
    #[\Override]
    public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData)
    {
        $pageIDs = [];
        if (!empty($embeddedData['wsp'])) {
            for ($i = 0, $length = \count($embeddedData['wsp']); $i < $length; $i++) {
                $pageIDs[] = \intval($embeddedData['wsp'][$i][0]);
            }
        }

        return \array_unique($pageIDs);
    }

    #[\Override]
    public function loadObjects(array $objectIDs)
    {
        $pages = [];

        foreach ($objectIDs as $objectID) {
            $page = PageCache::getInstance()->getPage($objectID);
            if ($page !== null) {
                $pages[$objectID] = $page;
            }
        }

        return $pages;
    }

    #[\Override]
    public function validateValues(string $objectType, int $objectID, array $values)
    {
        // Pages can be referenced as `123#Some Text`, where everything after the number
        // is a comment for better readability. Converting the values to integers via
        // `intval()` will discard the everything after the ID.
        $values = ArrayUtil::toIntegerArray($values);

        return \array_filter($values, static function ($value) {
            return PageCache::getInstance()->getPage($value) !== null;
        });
    }

    #[\Override]
    public function replaceSimple(string $objectType, int $objectID, string|int $value, array $attributes)
    {
        $page = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.page', $value);
        if ($page === null) {
            return null;
        }

        \assert($page instanceof Page);

        return match ($attributes['return'] ?? 'link') {
            'title' => $page->getTitle(),
            default => $page->getLink()
        };
    }
}
