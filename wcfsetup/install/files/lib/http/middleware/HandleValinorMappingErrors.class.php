<?php

namespace wcf\http\middleware;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\valinor\formatter\PrependPath;

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
            $messages = Messages::flattenFromNode($e->node())
                ->formatWith(new PrependPath());

            return new JsonResponse(
                [
                    'message' => "Could not map type '{$e->node()->type()}'.",
                    'errors' => \array_map(
                        static fn (NodeMessage $m) => $m->toString(),
                        \iterator_to_array($messages, false)
                    ),
                ],
                400,
                [],
                \JSON_PRETTY_PRINT
            );
        }
    }
}
