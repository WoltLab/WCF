<?php

namespace wcf\system\endpoint;

use CuyZ\Valinor\Mapper\MappingError;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handles incoming API requests, relying on the `RequestType` attributes to
 * register the endpoint. The endpoint can contain placeholders for parameters.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
interface IController
{
    /**
     * Invokes the controller, passing in any placeholders from the endpoint in
     * the `$variables` array.
     *
     * @param array<string, string> $variables
     * @throws MappingError
     */
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface;
}
