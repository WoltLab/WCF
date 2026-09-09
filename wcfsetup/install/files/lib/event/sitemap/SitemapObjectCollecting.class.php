<?php

namespace wcf\event\sitemap;

use wcf\data\object\type\ObjectTypeCache;
use wcf\event\IPsr14Event;
use wcf\system\sitemap\object\RegisteredSitemapObject;

/**
 * Requests the collection of objects that should be included in the sitemap.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SitemapObjectCollecting implements IPsr14Event
{
    /**
     * @var array<string, RegisteredSitemapObject>
     */
    private array $objects = [];

    public function __construct()
    {
        // Support for the deprecated object type definition `com.woltlab.wcf.sitemap.object`.
        $objectTypes = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.sitemap.object');
        foreach ($objectTypes as $objectType) {
            $this->register(RegisteredSitemapObject::fromObjectType($objectType));
        }
    }

    /**
     * Registers a new sitemap object.
     */
    public function register(RegisteredSitemapObject $object): void
    {
        $this->objects[$object->getObjectName()] = $object;
    }

    /**
     * @return array<string, RegisteredSitemapObject>
     */
    public function getObjects(): array
    {
        return $this->objects;
    }
}
