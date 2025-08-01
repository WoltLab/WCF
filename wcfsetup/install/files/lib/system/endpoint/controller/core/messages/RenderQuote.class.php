<?php

namespace wcf\system\endpoint\controller\core\messages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\DatabaseObject;
use wcf\data\IEmbeddedMessageObject;
use wcf\data\IMessage;
use wcf\data\user\UserProfile;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Retrieves data for the rendering of a quote.
 *
 * @author    Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
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

        // @phpstan-ignore argument.templateType
        $object = Helper::fetchObjectFromRequestParameter($parameters->objectID, $parameters->className);
        \assert($object instanceof IMessage && $object instanceof DatabaseObject);

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($object->getUserID());
        if ($userProfile === null) {
            $userProfile = UserProfile::getGuestUserProfile($object->getUsername());
        }

        if ($object instanceof IEmbeddedMessageObject) {
            $object->loadEmbeddedObjects();
        }

        return new JsonResponse(
            [
                "objectID" => $object->getObjectID(),
                "authorID" => $userProfile->getUserID(),
                "author" => $userProfile->getUsername(),
                "avatar" => $userProfile->getAvatar()->getURL(),
                "time" => (new \DateTime('@' . $object->getTime()))->format("c"),
                "title" => $object->getTitle(),
                "link" => $object->getLink(),
                "rawMessage" => $parameters->fullQuote ? $this->renderFullQuote($object) : null,
                "message" => $parameters->fullQuote ? $object->getFormattedMessage() : null
            ],
            200,
        );
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
        public readonly string $className,
        /** @var positive-int */
        public readonly int $objectID,
        public readonly bool $fullQuote = false,
    ) {}
}
