<?php

namespace wcf\system\interaction\user;

use wcf\data\DatabaseObject;
use wcf\data\ILinkableObject;
use wcf\data\ITitledObject;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Presents an interaction that opens the share dialog for an object.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class ShareInteraction extends AbstractInteraction
{
    public function __construct(
        protected readonly string|\Closure $languageItem = 'wcf.message.share',
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct('share', $isAvailableCallback);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        \assert($object instanceof ILinkableObject);
        \assert($object instanceof ITitledObject);

        $link = StringUtil::encodeHTML($object->getLink());
        $linkTitle = StringUtil::encodeHTML($object->getTitle());
        if (\is_string($this->languageItem)) {
            $label = WCF::getLanguage()->get($this->languageItem);
        } else {
            $label = ($this->languageItem)($object);
        }

        return <<<HTML
            <button
                type="button"
                class="wsShareButton"
                data-link="{$link}"
                data-link-title="{$linkTitle}"
            >
                {$label}
            </button>
            HTML;
    }
}
