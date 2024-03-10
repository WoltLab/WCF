<?php

namespace wcf\action;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\endpoint\controller\core\messages\MentionSuggestions;

/**
 * Suggests users that may be mentioned.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 * @deprecated 6.1
 */
final class EditorGetMentionSuggestionsAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $reflectionClass = new \ReflectionClass(MentionSuggestions::class);
        $relfectionMethod = $reflectionClass->getMethod('mentionSuggestions');
        $parameters = $relfectionMethod->getParameters();

        \assert(\count($parameters) === 1);
        \assert($parameters[0]->getName() === 'parameters');

        $type = $parameters[0]->getType();

        \assert($type instanceof \ReflectionNamedType);

        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            $type->getName(),
        );

        return (new MentionSuggestions())->mentionSuggestions($parameters);
    }
}
