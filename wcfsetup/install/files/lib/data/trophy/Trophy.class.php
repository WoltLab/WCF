<?php

namespace wcf\data\trophy;

use wcf\data\condition\Condition;
use wcf\data\DatabaseObject;
use wcf\data\ITitledLinkObject;
use wcf\data\trophy\category\TrophyCategory;
use wcf\data\trophy\category\TrophyCategoryCache;
use wcf\system\condition\ConditionHandler;
use wcf\system\event\EventHandler;
use wcf\system\request\IRouteController;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a user trophy.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @property-read   int $trophyID           unique id for the trophy
 * @property-read   string $title              the trophy title
 * @property-read   string $description            the trophy description
 * @property-read   int $categoryID         the categoryID of the trophy
 * @property-read   int $type               the trophy type
 * @property-read   string $iconFile           the file location of the icon
 * @property-read   string $iconName           the icon name
 * @property-read   string $iconColor          the icon color
 * @property-read   string $badgeColor         the icon badge color
 * @property-read   int $isDisabled         `1` if the trophy is disabled
 * @property-read   int $awardAutomatically     `1` if the trophy is awarded automatically
 * @property-read   int $revokeAutomatically        `1` if the trophy should be automatically revoked once the conditions are no longer met.
 * @property-read   int $trophyUseHtml              `1` if the trophy use a html description
 * @property-read   int $showOrder              position of the trophy in relation to the other trophies at the same location
 */
class Trophy extends DatabaseObject implements ITitledLinkObject, IRouteController
{
    /**
     * The type value, if this trophy is an image trophy.
     * @var int
     */
    public const TYPE_IMAGE = 1;

    /**
     * The type value, if this trophy is a badge trophy (based on CSS icons).
     * @var int
     */
    public const TYPE_BADGE = 2;

    /**
     * The default icon size.
     */
    public const DEFAULT_SIZE = 32;

    /**
     * Returns the title of the trophy.
     *
     * @since       5.3
     */
    public function __toString(): string
    {
        return $this->getTitle();
    }

    /**
     * @inheritDoc
     */
    public function getTitle(): string
    {
        return WCF::getLanguage()->get($this->title);
    }

    /**
     * @inheritDoc
     */
    public function getLink(): string
    {
        return LinkHandler::getInstance()->getLink('Trophy', [
            'object' => $this,
            'forceFrontend' => true,
        ]);
    }

    /**
     * Renders a trophy.
     *
     * @param int $size
     * @param bool $showTooltip
     * @return  string
     */
    public function renderTrophy($size = self::DEFAULT_SIZE, $showTooltip = false)
    {
        switch ($this->type) {
            case self::TYPE_IMAGE:
                return WCF::getTPL()->fetch('shared_trophyImage', 'wcf', [
                    'size' => $size,
                    'trophy' => $this,
                    'showTooltip' => $showTooltip,
                ], true);
                break;

            case self::TYPE_BADGE:
                return WCF::getTPL()->fetch('trophyBadge', 'wcf', [
                    'size' => $size,
                    'trophy' => $this,
                    'showTooltip' => $showTooltip,
                ], true);
                break;

            default:
                $parameters = [
                    'renderedTemplate' => null,
                    'size' => $size,
                    'showTooltip' => $showTooltip,
                ];

                EventHandler::getInstance()->fireAction($this, 'renderTrophy', $parameters);

                if ($parameters['renderedTemplate']) {
                    return $parameters['renderedTemplate'];
                }

                throw new \LogicException("Unable to render the trophy with the type '" . $this->type . "'.");
                break;
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
        if ($this->isDisabled) {
            return true;
        }

        if ($this->getCategory()->isDisabled) {
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
        if (!$this->trophyUseHtml) {
            return \nl2br(StringUtil::encodeHTML(WCF::getLanguage()->get($this->description)), false);
        }

        return WCF::getLanguage()->get($this->description);
    }

    /**
     * Returns the conditions of the trophy.
     *
     * @return  Condition[]
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
}
