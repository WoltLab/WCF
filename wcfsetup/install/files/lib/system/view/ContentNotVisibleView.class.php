<?php

namespace wcf\system\view;

use wcf\system\WCF;

/**
 * Represents the view for a 'content not visible' block.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class ContentNotVisibleView
{
    public function __construct(
        private readonly string $message,
    ) {
    }

    public function __toString(): string
    {
        return WCF::getTPL()->fetch('shared_contentNotVisible', 'wcf', [
            'message' => $this->message,
        ], true);
    }

    public static function forNotAvailable(): self
    {
        return new self(WCF::getLanguage()->getDynamicVariable('wcf.message.content.not.available.title'));
    }

    public static function forNoPermission(): self
    {
        return new self(WCF::getLanguage()->getDynamicVariable('wcf.message.content.no.permission.title'));
    }
}
