<?php

namespace wcf\system\sitemap;

use wcf\event\sitemap\SitemapObjectCollecting;
use wcf\system\event\EventHandler;
use wcf\system\registry\RegistryHandler;
use wcf\system\SingletonFactory;
use wcf\system\sitemap\object\RegisteredSitemapObject;

/**
 * Provides the sitemap objects and their configuration.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SitemapHandler extends SingletonFactory
{
    /**
     * Prefix for the configuration stored in the registry.
     */
    public const REGISTRY_PREFIX = 'sitemapData_';

    /**
     * @var array<string, RegisteredSitemapObject>
     */
    private array $objects;

    #[\Override]
    protected function init(): void
    {
        $event = new SitemapObjectCollecting();
        EventHandler::getInstance()->fire($event);

        $this->objects = $event->getObjects();
    }

    /**
     * @return array<string, RegisteredSitemapObject>
     */
    public function getObjects(): array
    {
        return $this->objects;
    }

    public function getObject(string $objectName): ?RegisteredSitemapObject
    {
        return $this->objects[$objectName] ?? null;
    }

    /**
     * Returns the rebuild time of the given object, taking the
     * configuration of the administrator into account.
     */
    public function getRebuildTime(RegisteredSitemapObject $object): int
    {
        $data = $this->getConfiguration($object->getObjectName());

        return isset($data['rebuildTime']) ? (int)$data['rebuildTime'] : $object->getRebuildTime();
    }

    /**
     * Returns whether the given object has been disabled by the administrator.
     */
    public function isDisabled(RegisteredSitemapObject $object): bool
    {
        $data = $this->getConfiguration($object->getObjectName());

        return isset($data['isDisabled']) ? (bool)$data['isDisabled'] : $object->isDisabled();
    }

    /**
     * Stores the configuration of the given object.
     */
    public function setConfiguration(
        RegisteredSitemapObject $object,
        int $rebuildTime,
        bool $isDisabled
    ): void {
        RegistryHandler::getInstance()->set(
            'com.woltlab.wcf',
            self::REGISTRY_PREFIX . $object->getObjectName(),
            \serialize([
                'rebuildTime' => $rebuildTime,
                'isDisabled' => $isDisabled ? 1 : 0,
            ])
        );
    }

    /**
     * Enables or disables the given object.
     */
    public function setIsDisabled(RegisteredSitemapObject $object, bool $isDisabled): void
    {
        $this->setConfiguration(
            $object,
            $this->getRebuildTime($object),
            $isDisabled
        );
    }

    /**
     * The configuration is read from the registry and may have been written by
     * an older version, therefore the type of the values is not guaranteed.
     *
     * @return array<string, mixed>
     */
    private function getConfiguration(string $objectName): array
    {
        $data = RegistryHandler::getInstance()->get(
            'com.woltlab.wcf',
            self::REGISTRY_PREFIX . $objectName
        );

        if ($data === null) {
            return [];
        }

        $data = @\unserialize($data);

        return \is_array($data) ? $data : [];
    }
}
