<?php

namespace wcf\http\middleware;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\error\HtmlErrorRenderer;
use wcf\http\Helper;
use wcf\system\valinor\formatter\PrependPath;
use wcf\system\WCF;

/**
 * Catches Valinor's MappingErrors and returns a HTTP 400 Bad Request.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class HandleValinorMappingErrors implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (MappingError $e) {
            $message = "Could not map type '{$e->node()->type()}'.";
            $errors = Messages::flattenFromNode($e->node())
                ->formatWith(new PrependPath());

            $preferredType = Helper::getPreferredContentType($request, [
                'application/json',
                'text/html',
            ]);

            return match ($preferredType) {
                'application/json' => new JsonResponse(
                    [
                        'message' => $message,
                        'exception' => \ENABLE_DEBUG_MODE ? $e->__toString() : null,
                        'errors' => \array_map(
                            static fn (NodeMessage $m) => $m->toString(),
                            \iterator_to_array($errors, false)
                        ),
                    ],
                    400,
                    [],
                    \JSON_PRETTY_PRINT
                ),
                'text/html' => new HtmlResponse(
                    (new HtmlErrorRenderer())->render(
                        WCF::getLanguage()->getDynamicVariable('wcf.global.error.title'),
                        $message,
                        $e
                    ),
                    400
                ),
            };
        }
    }
}
