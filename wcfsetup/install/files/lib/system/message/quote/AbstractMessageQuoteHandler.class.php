<?php

namespace wcf\system\message\quote;

use wcf\data\IMessage;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\NotImplementedException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Default implementation for quote handlers.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractMessageQuoteHandler extends SingletonFactory implements IMessageQuoteHandler
{
    /**
     * template name
     * @var string
     * @deprecated 6.2
     */
    public $templateName = 'messageQuoteList';

    /**
     * list of quoted message
     * @var QuotedMessage[]
     * @deprecated 6.2
     */
    public $quotedMessages = [];

    /**
     * Renders a template for given quotes.
     *
     * @param mixed[][] $data
     * @param bool $supportPaste
     * @return string
     * @deprecated 6.2 Implement `getMessage()` instead.
     */
    public function render(array $data, $supportPaste = false)
    {
        $messages = $this->getMessages($data);
        $this->overrideIsFullQuote($messages);

        $userIDs = $userProfiles = [];
        foreach ($messages as $message) {
            $userID = $message->getUserID();
            if ($userID) {
                $userIDs[] = $userID;
            }
        }

        if (!empty($userIDs)) {
            $userIDs = \array_unique($userIDs);
            $userProfiles = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
        }

        return WCF::getTPL()->render('wcf', $this->templateName, [
            'messages' => $messages,
            'supportPaste' => $supportPaste,
            'userProfiles' => $userProfiles,
        ]);
    }

    /**
     * Renders a list of quotes for insertation.
     *
     * @param mixed[][] $data
     * @param bool $render
     * @param bool $renderAsString
     * @return  string[]
     * @deprecated 6.2 Implement `getMessage()` instead.
     */
    public function renderQuotes(array $data, $render = true, $renderAsString = true)
    {
        $messages = $this->getMessages($data);
        $this->overrideIsFullQuote($messages);

        $renderedQuotes = [];
        foreach ($messages as $message) {
            foreach ($message as $quoteID => $quote) {
                $quotedMessage = $message->getFullQuote($quoteID);
                if ($render && ($renderAsString && $quotedMessage === null)) {
                    $renderedQuotes[] = MessageQuoteManager::getInstance()->renderQuote(
                        $message->object,
                        $quote,
                        $renderAsString
                    );
                } else {
                    $renderedQuotes[] = MessageQuoteManager::getInstance()->renderQuote(
                        $message->object,
                        ($quotedMessage === null ? $quote : $quotedMessage),
                        $renderAsString
                    );
                }
            }
        }

        return $renderedQuotes;
    }

    /**
     * Overrides the full quote flag for given message.
     *
     * @param QuotedMessage[] $messages
     * @return void
     * @deprecated 6.2
     */
    protected function overrideIsFullQuote(array $messages)
    {
        foreach ($messages as $message) {
            $quoteIDs = $message->getQuoteIDs();
            foreach ($quoteIDs as $quoteID) {
                $message->setOverrideIsFullQuote($quoteID, MessageQuoteManager::getInstance()->isFullQuote($quoteID));
            }
        }
    }

    /**
     * Returns a list of QuotedMessage objects.
     *
     * @param mixed[][] $data
     * @return QuotedMessage[]
     * @deprecated 6.2 Implement `getMessage()` instead.
     */
    protected function getMessages(array $data)
    {
        throw new NotImplementedException();
    }

    /**
     * @return list<QuotedMessage>
     * @deprecated 6.2
     */
    public function legacyGetMessages(int $objectID, string $marker): array
    {
        return $this->getMessages([
            $objectID => [$marker],
        ]);
    }

    #[\Override]
    public function getMessage(int $objectID): ?IMessage
    {
        throw new NotImplementedException();
    }
}
