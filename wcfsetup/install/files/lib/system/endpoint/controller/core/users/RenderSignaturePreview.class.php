<?php

namespace wcf\system\endpoint\controller\core\users;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\WCF;

/**
 * Renders the preview of the active user's signature.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/users/signature/preview')]
final class RenderSignaturePreview implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (
            WCF::getUser()->isGuest()
            || WCF::getUser()->disableSignature !== 0
            || !WCF::getSession()->hasPermission('user.signature.canEditSignature')
        ) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapApiParameters($request, RenderSignaturePreviewParameters::class);

        BBCodeHandler::getInstance()->setDisallowedBBCodesFor(
            'user.signature.disallowedBBCodes',
            'com.woltlab.wcf.user.signature'
        );

        $htmlInputProcessor = new HtmlInputProcessor();
        $htmlInputProcessor->process(
            $parameters->message,
            'com.woltlab.wcf.user.signature',
            WCF::getUser()->userID
        );

        $disallowedBBCodes = $htmlInputProcessor->validate();
        if ($disallowedBBCodes !== []) {
            throw new UserInputException(
                'message',
                WCF::getLanguage()->getDynamicVariable('wcf.message.error.disallowedBBCodes', [
                    'disallowedBBCodes' => $disallowedBBCodes,
                ])
            );
        }

        MessageEmbeddedObjectManager::getInstance()->registerTemporaryMessage($htmlInputProcessor);

        $htmlOutputProcessor = new HtmlOutputProcessor();
        $htmlOutputProcessor->process(
            $htmlInputProcessor->getHtml(),
            'com.woltlab.wcf.user.signature',
            WCF::getUser()->userID
        );

        return new JsonResponse([
            'message' => $htmlOutputProcessor->getHtml(),
        ]);
    }
}

/** @internal */
final class RenderSignaturePreviewParameters
{
    public function __construct(
        public readonly string $message,
    ) {}
}
