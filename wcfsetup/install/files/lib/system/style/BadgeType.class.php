<?php

namespace wcf\system\style;

use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Semantic badge types used to indicate the status of content, each with a
 * unique color and icon.
 *
 * @author Marcel Werk
 * @copyright 2001-2026 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
enum BadgeType: string
{
    case Deleted = 'deleted';
    case Disabled = 'disabled';
    case Done = 'done';
    case Featured = 'featured';
    case Official = 'official';
    case Scheduled = 'scheduled';

    public function getIcon(): FontAwesomeIcon
    {
        return match ($this) {
            self::Deleted => FontAwesomeIcon::fromValues('trash-can'),
            self::Disabled => FontAwesomeIcon::fromValues('eye-slash'),
            self::Done => FontAwesomeIcon::fromValues('circle-check'),
            self::Featured => FontAwesomeIcon::fromValues('star'),
            self::Official => FontAwesomeIcon::fromValues('shield-halved'),
            self::Scheduled => FontAwesomeIcon::fromValues('clock'),
        };
    }

    public function getLanguageItem(): string
    {
        return 'wcf.message.status.' . $this->value;
    }

    public function getCssClassName(): string
    {
        return 'badge--' . $this->value;
    }

    /**
     * Renders the badge, `$languageItem` replaces the default label of this type.
     */
    public function toHtml(string $additionalClassNames = '', ?string $languageItem = null): string
    {
        $className = 'badge ' . $this->getCssClassName();
        if ($additionalClassNames !== '') {
            $className .= ' ' . $additionalClassNames;
        }

        return \sprintf(
            '<span class="%s">%s <span>%s</span></span>',
            StringUtil::encodeHTML($className),
            $this->getIcon()->toHtml(),
            WCF::getLanguage()->get($languageItem ?? $this->getLanguageItem()),
        );
    }
}
