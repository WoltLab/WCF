<?php

namespace wcf\action;

use CuyZ\Valinor\Mapper\MappingError;
use CuyZ\Valinor\Mapper\Source\Source;
use CuyZ\Valinor\Mapper\Tree\Message\MessageBuilder;
use CuyZ\Valinor\Mapper\Tree\Message\MessagesFlattener;
use CuyZ\Valinor\Mapper\TreeMapper;
use CuyZ\Valinor\MapperBuilder;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\TextResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\http\middleware\Xsrf;
use wcf\system\exception\InvalidSecurityTokenException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Updates the user's timezone option.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Action
 * @since 6.0
 */
final class UserTimezoneSyncAction implements RequestHandlerInterface
{
    private const PARAMETERS = <<<'EOT'
        array {
            tz: \DateTimeZone
        }
        EOT;

    private TreeMapper $mapper;

    public function __construct()
    {
        $this->mapper = (new MapperBuilder())
            ->registerConstructor(
                static function (string $tz): \DateTimeZone {
                    try {
                        return new \DateTimeZone($tz);
                    } catch (\Exception) {
                        throw MessageBuilder::newError('The timezone {tz} is not valid.')
                            ->withParameter('tz', $tz)
                            ->build();
                    }
                }
            )
            ->mapper();
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getAttribute(Xsrf::HAS_VALID_HEADER_ATTRIBUTE) !== true) {
            throw new InvalidSecurityTokenException();
        }

        if (!WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }

        try {
            $parameters = $this->mapper->map(
                self::PARAMETERS,
                Source::json($request->getBody())
            );

            $action = new UserAction(
                [
                    WCF::getUser(),
                ],
                'update',
                [
                    'options' => [
                        User::getUserOptionID('timezone') => $parameters['tz']->getName(),
                    ],
                ]
            );
            $action->executeAction();

            return new EmptyResponse();
        } catch (MappingError $error) {
            $node = $error->node();
            $messages = new MessagesFlattener($node);

            return new TextResponse(\implode("\n", \iterator_to_array($messages)), 400);
        }
    }
}
