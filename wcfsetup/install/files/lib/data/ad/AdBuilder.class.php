<?php

namespace wcf\data\ad;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\cache\builder\AdCacheBuilder;
use wcf\system\cache\builder\ConditionCacheBuilder;
use wcf\system\condition\ConditionHandler;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting ads.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Ad>
 */
final class AdBuilder extends DatabaseObjectBuilder
{
    private ?int $showOrder = null;

    /**
     * Sets the id of the `com.woltlab.wcf.adLocation` object type the ad is shown at.
     */
    public function setObjectTypeID(int $objectTypeID): static
    {
        $this->properties['objectTypeID'] = $objectTypeID;

        return $this;
    }

    public function setAdName(string $adName): static
    {
        $this->properties['adName'] = $adName;

        return $this;
    }

    /**
     * Sets the HTML code of the ad.
     */
    public function setAd(string $ad): static
    {
        $this->properties['ad'] = $ad;

        return $this;
    }

    public function setIsDisabled(bool $isDisabled): static
    {
        $this->properties['isDisabled'] = (int)$isDisabled;

        return $this;
    }

    /**
     * Sets the position of the ad in relation to the other ads at the same
     * location.
     *
     * The other ads are shifted accordingly, a value of `0` appends the ad at
     * the end.
     */
    public function setShowOrder(int $showOrder): static
    {
        $this->showOrder = $showOrder;

        return $this;
    }

    /**
     * Sets the serialized object filters that the active user must match.
     *
     * An ad without any filters is visible to everyone, therefore an empty
     * list of filters is stored as `null`.
     */
    public function setConditions(?string $conditions): static
    {
        if ($conditions !== null && \json_decode($conditions, true) === []) {
            $conditions = null;
        }

        $this->properties['conditions'] = $conditions;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['objectTypeID', 'adName'];
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        self::applyShowOrder($object, $this->showOrder ?? 0);
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        $previousAd = $this->getObject();

        if ($object->objectTypeID !== $previousAd->objectTypeID) {
            // The ad is moved to another location, therefore its position
            // must be determined in relation to the ads at the new location.
            self::applyShowOrder($object, $this->showOrder ?? 0);
        } elseif ($this->showOrder !== null && $this->showOrder !== $previousAd->showOrder) {
            self::applyShowOrder($object, $this->showOrder);
        }
    }

    #[\Override]
    protected static function beforeDeleteAll(array $objectIDs): void
    {
        ConditionHandler::getInstance()->deleteConditions('com.woltlab.wcf.condition.ad', $objectIDs);
    }

    /**
     * Moves the ad to the given position among the ads at the same location,
     * shifting the ads that follow it. A value of `0` appends the ad at the end.
     */
    private static function applyShowOrder(Ad $ad, int $showOrder): void
    {
        $sql = "SELECT  MAX(showOrder)
                FROM    wcf1_ad
                WHERE   objectTypeID = ?
                    AND adID <> ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $ad->objectTypeID,
            $ad->adID,
        ]);
        $maxShowOrder = $statement->fetchSingleColumn();
        if ($maxShowOrder === null) {
            $maxShowOrder = 0;
        }

        if ($showOrder === 0 || $showOrder > $maxShowOrder) {
            $newShowOrder = $maxShowOrder + 1;
        } else {
            $sql = "UPDATE  wcf1_ad
                    SET     showOrder = showOrder + 1
                    WHERE   objectTypeID = ?
                        AND showOrder >= ?
                        AND adID <> ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                $ad->objectTypeID,
                $showOrder,
                $ad->adID,
            ]);

            $newShowOrder = $showOrder;
        }

        $sql = "UPDATE  wcf1_ad
                SET     showOrder = ?
                WHERE   adID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $newShowOrder,
            $ad->adID,
        ]);
    }

    public static function resetCache(): void
    {
        AdCacheBuilder::getInstance()->reset();
        ConditionCacheBuilder::getInstance()->reset([
            'definitionID' => ObjectTypeCache::getInstance()
                ->getDefinitionByName('com.woltlab.wcf.condition.ad')
                ->definitionID,
        ]);
    }
}
