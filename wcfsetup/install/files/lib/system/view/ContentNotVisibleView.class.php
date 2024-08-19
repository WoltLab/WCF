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
    private readonly string $message;

    public function __construct(
        string $message = '',
    ) {
        if (!$message) {
            $message = WCF::getLanguage()->get('wcf.message.content.not.available.title');
        }

        $this->message = $message;
    }

    public function __toString(): string
    {
        return WCF::getTPL()->fetch('shared_contentNotVisible', 'wcf', [
            'message' => $this->message,
        ], true);
    }
}
