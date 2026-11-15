<?php

namespace wcf\system\interaction\user;

use wcf\data\DatabaseObject;
use wcf\system\interaction\AbstractInteraction;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Presents an interaction that allows a user to quote the full content of a message.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class QuoteInteraction extends AbstractInteraction
{
    public function __construct(
        protected readonly string $objectType,
        protected readonly string|\Closure $languageItem = 'wcf.message.quote.quoteMessage',
        ?\Closure $isAvailableCallback = null
    ) {
        parent::__construct('quote', $isAvailableCallback);
    }

    #[\Override]
    public function isAvailable(DatabaseObject $object): bool
    {
        if ($this->isAvailableCallback === null) {
            // The quote handler is not part of the tiny build for guests.
            return !\VISITOR_USE_TINY_BUILD || !WCF::getUser()->isGuest();
        }

        return parent::isAvailable($object);
    }

    #[\Override]
    public function render(DatabaseObject $object): string
    {
        $objectType = StringUtil::encodeHTML($this->objectType);
        $objectID = StringUtil::encodeHTML((string)$object->getObjectID());
        if (\is_string($this->languageItem)) {
            $label = WCF::getLanguage()->get($this->languageItem);
        } else {
            $label = ($this->languageItem)($object);
        }

        return <<<HTML
            <button
                type="button"
                data-quote-message="{$objectType}"
                data-object-id="{$objectID}"
            >
                {$label}
            </button>
            HTML;
    }
}
