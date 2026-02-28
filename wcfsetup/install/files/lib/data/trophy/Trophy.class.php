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
 * @property-read   int     $trophyID               unique id for the trophy
 * @property-read   ?string $title
 * @property-read   ?string $description
 * @property-read   int     $categoryID
 * @property-read   ?int    $type
 * @property-read   ?string $iconFile
 * @property-read   ?string $iconName
 * @property-read   ?string $iconColor
 * @property-read   ?string $badgeColor
 * @property-read   0|1     $isDisabled             `1` if the trophy is disabled, otherwise `0`
 * @property-read   0|1     $awardAutomatically     `1` if the trophy is awarded automatically, otherwise `0`
 * @property-read   0|1     $revokeAutomatically    `1` if the trophy should be automatically revoked once the conditions are no longer met, otherwise `0`
 * @property-read   0|1     $trophyUseHtml          `1` if the trophy use a html description, otherwise `0`
 * @property-read   int     $showOrder              position of the trophy in relation to the other trophies at the same location
 */
class Trophy extends DatabaseObject implements ITitledLinkObject, IRouteController
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

                if ($parameters['renderedTemplate']) {
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
