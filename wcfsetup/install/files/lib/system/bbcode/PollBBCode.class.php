<?php

namespace wcf\system\bbcode;

use wcf\data\poll\Poll;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\view\ContentNotVisibleView;
use wcf\system\view\poll\PollWidgetView;
use wcf\util\StringUtil;

/**
 * Parses the [poll] bbcode tag that embeds a standalone poll.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PollBBCode extends AbstractBBCode
{
    #[\Override]
    public function getParsedTag(array $openingTag, string $content, array $closingTag, BBCodeParser $parser): string
    {
        $pollID = (int)($openingTag['attributes'][0] ?? 0);
        if ($pollID === 0) {
            return '';
        }

        /** @var ?Poll $poll */
        $poll = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.poll', $pollID);
        if ($poll === null || !$poll->isStandalone() || \MODULE_POLL === 0) {
            // Intentionally don't use `ContentNotVisibleView::forNotAvailable()`,
            // because deleted polls do not automatically remove the BBCode from the content.
            return '';
        }

        if (!$poll->isAccessible()) {
            return ContentNotVisibleView::forNoPermission();
        }

        if ($parser->getOutputType() === 'text/html') {
            return (string)new PollWidgetView($poll);
        }

        $options = \array_map(
            static fn($option) => $option->getOptionValue(),
            \array_values($poll->getOptions())
        );

        if ($parser->getOutputType() === 'text/simplified-html') {
            return \sprintf(
                '<strong>%s</strong><br>%s',
                StringUtil::encodeHTML($poll->getQuestion()),
                \implode('<br>', \array_map(
                    static fn(string $option) => '- ' . StringUtil::encodeHTML($option),
                    $options
                ))
            );
        }

        return StringUtil::encodeHTML($poll->getQuestion()) . "\n" . \implode("\n", \array_map(
            static fn(string $option) => '- ' . StringUtil::encodeHTML($option),
            $options
        ));
    }
}
