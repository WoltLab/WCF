<?php

namespace wcf\system\listView;

use wcf\system\style\FontAwesomeIcon;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a primary button shown in a list view header.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ListViewPrimaryButton
{
    private function __construct(
        public readonly string $languageItem,
        public readonly FontAwesomeIcon $icon,
        public readonly ?string $link = null,
        public readonly ?string $cssClassName = null
    ) {}

    public function render(): string
    {
        if ($this->link !== null) {
            return \sprintf(
                '<a href="%s" class="button buttonPrimary small">%s<span>%s</span></a>',
                StringUtil::encodeHTML($this->link),
                $this->icon->toHtml(16),
                WCF::getLanguage()->get($this->languageItem),
            );
        } else {
            return \sprintf(
                '<button type="button" class="button buttonPrimary small %s">%s<span>%s</span></button>',
                StringUtil::encodeHTML($this->cssClassName),
                $this->icon->toHtml(16),
                WCF::getLanguage()->get($this->languageItem),
            );
        }
    }

    public static function forLink(string $languageItem, FontAwesomeIcon $icon, string $link): self
    {
        return new self($languageItem, $icon, link: $link);
    }

    public static function forButton(string $languageItem, FontAwesomeIcon $icon, string $cssClassName): self
    {
        return new self($languageItem, $icon, cssClassName: $cssClassName);
    }
}
