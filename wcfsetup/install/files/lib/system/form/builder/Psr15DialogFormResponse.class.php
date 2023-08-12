<?php

namespace wcf\system\form\builder;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;

/**
 * Creates a response that is understood by the `[data-formbuilder]` implementation
 * as a shortcut for tasks like reloading the page or redirecting to another URL.
 *
 * @author Alexander Ebert
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class Psr15DialogFormResponse
{
    private readonly array $payload;

    private const RESPONSE_IDENTIFIER = "__Psr15DialogFormResponse";

    /**
     * Redirects the client to the provided URL.
     */
    public static function redirect(string $redirectUrl): self
    {
        return new self([
            "redirectUrl" => $redirectUrl,
        ]);
    }

    /**
     * Instructs the client to reload the page.
     */
    public static function reload(): self
    {
        return new self([
            "reload" => true,
        ]);
    }

    /**
     * Converts this into a PSR response.
     */
    public function toResponse(): ResponseInterface
    {
        return new JsonResponse([
            'payload' => $this->payload,
            self::RESPONSE_IDENTIFIER => true,
        ]);
    }

    private function __construct(array $payload)
    {
        $this->payload = $payload;
    }
}
