<?php

namespace wcf\system\sitemap\object;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;
use wcf\data\object\type\ObjectType;
use wcf\data\package\PackageCache;
use wcf\system\WCF;

/**
 * Represents a sitemap object that is registered with the
 * `wcf\event\sitemap\SitemapObjectCollecting` event.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class RegisteredSitemapObject
{
    public const DEFAULT_REBUILD_TIME = 604800;

    /**
     * @param $objectName Unique name of the sitemap object, e.g. `com.example.foo.sitemap.object.bar`.
     * @param ISitemapObjectObjectType<DatabaseObjectList<DatabaseObject>> $processor
     * @param $packageID Package that owns the generated sitemap files, defaults to the core.
     * @param $name Localized name of the sitemap object, defaults to the phrase
     *              `wcf.acp.sitemap.objectType.{objectName}`.
     */
    public function __construct(
        private readonly string $objectName,
        private readonly ISitemapObjectObjectType $processor,
        private readonly int $rebuildTime = self::DEFAULT_REBUILD_TIME,
        private readonly ?int $packageID = null,
        private readonly bool $isDisabled = false,
        private readonly string $name = '',
    ) {}

    /**
     * Creates a sitemap object from a legacy object type of the deprecated
     * object type definition `com.woltlab.wcf.sitemap.object`.
     */
    public static function fromObjectType(ObjectType $objectType): self
    {
        $processor = $objectType->getProcessor();
        \assert($processor instanceof ISitemapObjectObjectType);

        return new self(
            $objectType->objectType,
            $processor,
            $objectType->rebuildTime !== null ? (int)$objectType->rebuildTime : self::DEFAULT_REBUILD_TIME,
            $objectType->packageID,
            (bool)$objectType->isDisabled,
        );
    }

    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * Returns the localized name of this sitemap object.
     */
    public function getName(): string
    {
        if ($this->name !== '') {
            return $this->name;
        }

        return WCF::getLanguage()->getDynamicVariable(
            \sprintf('wcf.acp.sitemap.objectType.%s', $this->objectName)
        );
    }

    /**
     * @return ISitemapObjectObjectType<DatabaseObjectList<DatabaseObject>>
     */
    public function getProcessor(): ISitemapObjectObjectType
    {
        return $this->processor;
    }

    /**
     * Returns the rebuild time configured by the developer. The value that is
     * actually used may be overwritten by the administrator, see
     * `wcf\system\sitemap\SitemapHandler::getRebuildTime()`.
     */
    public function getRebuildTime(): int
    {
        return $this->rebuildTime;
    }

    /**
     * Returns the default state configured by the developer. The state that is
     * actually used may be overwritten by the administrator, see
     * `wcf\system\sitemap\SitemapHandler::isDisabled()`.
     */
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function getPackageID(): int
    {
        return $this->packageID ?? PackageCache::getInstance()->getPackageID('com.woltlab.wcf');
    }
}
