<?php

namespace wcf\system\event\listener;

use wcf\event\moderation\queue\SpamDetectionRendering;
use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Renders the matches in the Stop Forum Spam data that caused a message to be flagged
 * as spam by `MessageSpamCheckingSfsListener`.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class SpamDetectionRenderingSfsListener
{
    private const MATCH_PHRASES = [
        'username' => 'wcf.user.username',
        'email' => 'wcf.user.email',
        'ip' => 'wcf.user.ipAddress',
    ];

    public function __invoke(SpamDetectionRendering $event): void
    {
        if ($event->identifier !== 'com.woltlab.wcf') {
            return;
        }

        $matches = $event->data['matches'] ?? null;
        if (!\is_array($matches)) {
            return;
        }

        $matchLabels = [];
        foreach ($matches as $match) {
            if (!\is_string($match) || !isset(self::MATCH_PHRASES[$match])) {
                continue;
            }

            $matchLabels[$match] = WCF::getLanguage()->get(self::MATCH_PHRASES[$match]);
        }

        if ($matchLabels === []) {
            return;
        }

        $event->setDetails(HtmlString::fromSafeHtml(
            WCF::getLanguage()->getDynamicVariable('wcf.moderation.suspectedSpam.blacklistMatches', [
                'matchLabels' => \array_values($matchLabels),
            ])
        ));
    }
}
