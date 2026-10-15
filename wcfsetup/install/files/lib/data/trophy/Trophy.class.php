<?php

namespace wcf\data\trophy;

use wcf\data\CollectionDatabaseObject;
use wcf\data\condition\Condition;
use wcf\data\file\File;
use wcf\data\ITitledLinkObject;
use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\page\TrophyPage;
use wcf\system\cache\runtime\FileRuntimeCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\event\EventHandler;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user trophy.
 *
 * The localized title and description are stored in the `wcf1_trophy_l10n` table.
 *
 * @author  Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int     $trophyID               unique id for the trophy
 * @property-read   int     $categoryID
 * @property-read   int     $type
 * @property-read   ?int    $iconFileID             id of the file of the image or `null` if the trophy is a badge or the file has been deleted
 * @property-read   ?string $iconName
 * @property-read   ?string $iconColor
 * @property-read   ?string $badgeColor
 * @property-read   0|1     $isDisabled             `1` if the trophy is disabled, otherwise `0`
 * @property-read   0|1     $awardAutomatically     `1` if the trophy is awarded automatically, otherwise `0`
 * @property-read   0|1     $revokeAutomatically    `1` if the trophy should be automatically revoked once the conditions are no longer met, otherwise `0`
 * @property-read   0|1     $trophyUseHtml          `1` if the trophy use a html description, otherwise `0`
 * @property-read   int     $showOrder              position of the trophy in relation to the other trophies at the same location
 * @property-read   ?string $conditions             JSON-encoded object filters that a user must match to be awarded automatically
 *
 * @extends CollectionDatabaseObject<TrophyCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
class Trophy extends CollectionDatabaseObject implements ITitledLinkObject, IRouteController, \Stringable
{
    /**
     * The type value, if this trophy is an image trophy.
     * @var int
     */
    const TYPE_IMAGE = 1;

    /**
     * The type value, if this trophy is a badge trophy (based on CSS icons).
     * @var int
     */
    const TYPE_BADGE = 2;

    /**
     * The default icon size.
     */
    const DEFAULT_SIZE = 32;

    private ?File $iconFile = null;

    /**
     * Returns the title of the trophy.
     *
     * @since       5.3
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->getTitle();
    }

    #[\Override]
    public function getTitle(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'title');
    }

    #[\Override]
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getControllerLink(TrophyPage::class, [
            'object' => $this,
        ]);
    }

    /**
     * Renders a trophy.
     *
     * @return  string
     */
    public function renderTrophy(int $size = self::DEFAULT_SIZE, bool $showTooltip = false)
    {
        switch ($this->type) {
            case self::TYPE_IMAGE:
                return WCF::getTPL()->render('wcf', 'shared_trophyImage', [
                    'size' => $size,
                    'trophy' => $this,
                    'showTooltip' => $showTooltip,
                ]);

            case self::TYPE_BADGE:
                return WCF::getTPL()->render('wcf', 'shared_trophyBadge', [
                    'size' => $size,
                    'trophy' => $this,
                    'showTooltip' => $showTooltip,
                ]);

            default:
                $parameters = [
                    'renderedTemplate' => null,
                    'size' => $size,
                    'showTooltip' => $showTooltip,
                ];

                EventHandler::getInstance()->fireAction($this, 'renderTrophy', $parameters);

                if ($parameters['renderedTemplate'] !== null) {
                    return $parameters['renderedTemplate'];
                }

                throw new \LogicException("Unable to render the trophy with the type '" . $this->type . "'.");
        }
    }

    /**
     * Returns the category for this trophy.
     *
     * @return  TrophyCategory
     */
    public function getCategory()
    {
        return TrophyCategoryCache::getInstance()->getCategoryByID($this->categoryID);
    }

    /**
     * Returns true if the current trophy is disabled. Returns also true if the trophy category is disabled.
     *
     * @return  bool
     */
    public function isDisabled()
    {
        if ($this->isDisabled !== 0) {
            return true;
        }

        if ($this->getCategory()->isDisabled !== 0) {
            return true;
        }

        return false;
    }

    /**
     * Returns the parsed description for the trophy.
     *
     * @return  string
     */
    public function getDescription()
    {
        $description = $this->getCollection()->getResolvedL10nValue($this, 'description');
        if ($this->trophyUseHtml === 0) {
            return \nl2br(StringUtil::encodeHTML($description), false);
        }

        return $description;
    }

    /**
     * Returns the localized values of the given column as a
     * `languageID => value` map (see `L10nStorage`).
     *
     * @return L10nValue
     * @since 6.3
     */
    public function getL10nValues(string $columnName): array
    {
        if ($columnName !== 'title' && $columnName !== 'description') {
            throw new \InvalidArgumentException("Invalid column name given.");
        }

        return $this->getCollection()->getL10nValues($this, $columnName);
    }

    /**
     * Returns the conditions of the trophy.
     *
     * @return  Condition[]
     * @deprecated 6.3 Use object filter API instead.
     */
    public function getConditions()
    {
        return ConditionHandler::getInstance()->getConditions('com.woltlab.wcf.condition.trophy', $this->trophyID);
    }

    /**
     * @since 6.0
     */
    public function getIcon(): ?FontAwesomeIcon
    {
        if ($this->type === self::TYPE_BADGE) {
            return FontAwesomeIcon::fromString($this->iconName);
        }

        return null;
    }

    /**
     * Returns the url of the image of an image trophy.
     *
     * @since 6.3
     */
    public function getIconPath(): string
    {
        $file = $this->getIconFile();
        if ($file === null) {
            // The file is deleted immediately from within the form, leaving the
            // trophy without an image until the form is saved.
            return 'data:image/svg+xml;base64,' . \base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">'
                    . '<rect width="24" height="24" rx="3" fill="#f0c"/>'
                    . '<text x="12" y="12" fill="#fff" font-family="sans-serif" font-size="18" text-anchor="middle" dominant-baseline="central">?</text>'
                    . '</svg>'
            );
        }

        return $file->getFullSizeImageSource() ?? $file->getLink();
    }

    /**
     * @since 6.3
     */
    public function getIconFile(): ?File
    {
        if ($this->iconFileID === null) {
            return null;
        }

        $this->iconFile ??= FileRuntimeCache::getInstance()->getObject($this->iconFileID);

        return $this->iconFile;
    }

    /**
     * @since 6.3
     */
    public function setIconFile(File $file): void
    {
        \assert($file->fileID === $this->iconFileID);

        $this->iconFile = $file;
    }

    /**
     * @since 6.3
     */
    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_trophy',
            'wcf1_trophy_l10n',
            'trophyID',
            ['title', 'description'],
            maximumLengths: ['title' => 255],
        );
    }
}
