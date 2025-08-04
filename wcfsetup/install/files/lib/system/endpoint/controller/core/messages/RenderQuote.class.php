<?php

namespace wcf\system\endpoint\controller\core\messages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\IMessage;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\NotImplementedException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\message\quote\IMessageQuoteHandler;

/**
 * Retrieves data for the rendering of a quote.
 *
 * @author    Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license   GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since     6.2
 */
#[GetRequest('/core/messages/render-quote')]
final class RenderQuote implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $parameters = Helper::mapApiParameters($request, GetRenderQuoteParameters::class);

        $objectType = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.message.quote',
            $parameters->objectType,
        );
        $processor = $objectType->getProcessor();
        \assert($processor instanceof IMessageQuoteHandler);

        $message = null;
        try {
            $message = $processor->getMessage($parameters->objectID);
        } catch (NotImplementedException) {
            // This can happen for legacy implementations that do not yet
            // implement the new `getMessage()` method.
        }

        if ($message === null) {
            throw new PermissionDeniedException();
        }

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($message->getUserID());
        if ($userProfile === null) {
            $userProfile = UserProfile::getGuestUserProfile($message->getUsername());
        }

        return new JsonResponse([
            'objectID' => $parameters->objectID,
            'author' => $userProfile->getUsername(),
            'avatar' => $userProfile->getAvatar()->getURL(),
            'link' => $message->getLink(),
            'rawMessage' => $parameters->isFullQuote ? $this->renderFullQuote($message) : null,
            'message' => $parameters->isFullQuote ? $message->getFormattedMessage() : null
        ]);
    }

    private function renderFullQuote(IMessage $object): string
    {
        $htmlInputProcessor = new HtmlInputProcessor();
        $htmlInputProcessor->processIntermediate($object->getMessage());

        if (MESSAGE_MAX_QUOTE_DEPTH) {
            $htmlInputProcessor->enforceQuoteDepth(MESSAGE_MAX_QUOTE_DEPTH - 1, true);
        }

        return $htmlInputProcessor->getHtml();
    }
}

/** @internal */
final class GetRenderQuoteParameters
{
    public function __construct(
        /** @var non-empty-string */
        public readonly string $objectType,
        /** @var positive-int */
        public readonly int $objectID,

        public readonly bool $isFullQuote,
    ) {}
}
