<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Intercepts request processing if a startup error is detected that
 * makes it impossible to safely proceed with processing. An example would
 * be 'max_input_vars' silently dropping request parameters.
 *
 * Startup errors caused by a malformed request are answered with a 400
 * without logging them outside of the debug mode, because no conforming
 * client sends such a request and they are frequently caused by bots.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class HandleStartupErrors implements MiddlewareInterface
{
    private const MALFORMED_REQUEST_ERRORS = [
        'PHP Request Startup: Missing boundary in multipart/form-data POST data',
        'PHP Request Startup: Invalid boundary in multipart/form-data POST data',
        // Raised through `sapi_error()`, which does not add the prefix.
        'Boundary too large in multipart/form-data POST data',
        'PHP Request Startup: File Upload Mime headers garbled',
    ];

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (\defined('WCF_STARTUP_ERROR')) {
            if (\ENABLE_DEBUG_MODE === 0 && $this->isMalformedRequest(\WCF_STARTUP_ERROR['message'], $request)) {
                return new TextResponse('Failed to parse the incoming request.', 400);
            }

            throw new \ErrorException(\WCF_STARTUP_ERROR['message'], 0, \WCF_STARTUP_ERROR['type']);
        }

        return $handler->handle($request);
    }

    private function isMalformedRequest(string $message, ServerRequestInterface $request): bool
    {
        // The message embeds the configured `max_input_nesting_level`.
        if (\str_starts_with($message, 'PHP Request Startup: Input variable nesting level exceeded ')) {
            return true;
        }

        // The session upload progress starts a session while the request body
        // is parsed, which fails for an invalid session ID. WoltLab Suite never
        // sends this field, it is used by requests probing for file inclusions.
        // PHP before 8.4.27 continues after the failed read, ending in a failed write.
        if (
            \str_starts_with($message, 'PHP Request Startup: Failed to read session data: ')
            || \str_starts_with($message, 'PHP Request Startup: Failed to write session data (')
        ) {
            $body = $request->getParsedBody();

            return \is_array($body) && isset($body[\ini_get('session.upload_progress.name')]);
        }

        return \in_array($message, self::MALFORMED_REQUEST_ERRORS, true);
    }
}
