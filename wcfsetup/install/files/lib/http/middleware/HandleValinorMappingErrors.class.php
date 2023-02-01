<?php

namespace wcf\http\middleware;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Tree\Message\Messages;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;
use GuzzleHttp\Psr7\Header;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
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

            if ($this->prefersJson($request)) {
                return new JsonResponse(
                    [
                        'message' => $message,
                        'errors' => \array_map(
                            static fn (NodeMessage $m) => $m->toString(),
                            \iterator_to_array($errors, false)
                        ),
                    ],
                    400,
                    [],
                    \JSON_PRETTY_PRINT
                );
            } else {
                // TODO: Create a more generically reusable template for this type of error message.
                return new HtmlResponse(
                    WCF::getTPL()->fetchStream(
                        'userException',
                        'wcf',
                        [
                            'name' => $e::class,
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'message' => $message,
                            'stacktrace' => $e->getTraceAsString(),
                            'templateName' => 'userException',
                            'templateNameApplication' => 'wcf',
                            'exceptionClassName' => $e::class,
                        ]
                    ),
                    400
                );
            }
        }
    }

    // TODO: Move this into a reusable function.
    private function prefersJson(MessageInterface $m)
    {
        if (!$m->hasHeader('accept')) {
            return false;
        }

        $headers = Header::parse($m->getHeaderLine('accept'));
        
        \usort($headers, static function ($a, $b) {
            return ($b['q'] ?? 1) <=> ($a['q'] ?? 1);
        });

        return isset($headers[0][0]) && $headers[0][0] === 'application/json';
    }
}
