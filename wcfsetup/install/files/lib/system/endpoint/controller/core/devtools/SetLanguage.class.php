<?php

namespace wcf\system\endpoint\controller\core\devtools;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\command\user\UpdateUser;
use wcf\data\user\UserBuilder;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Sets the language of the active user through the developer tools.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/devtools/set-language')]
final class SetLanguage implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertDeveloperToolsAreAvailable();

        $parameters = Helper::mapApiParameters($request, SetLanguageParameters::class);

        if (LanguageFactory::getInstance()->getLanguage($parameters->languageID) === null) {
            throw new UserInputException('languageID', 'invalid');
        }

        new UpdateUser(
            UserBuilder::forUpdate(WCF::getUser())->setLanguageID($parameters->languageID)
        )();

        return new JsonResponse([]);
    }

    private function assertDeveloperToolsAreAvailable(): void
    {
        if (\ENABLE_DEBUG_MODE === 0 || \ENABLE_DEVELOPER_TOOLS === 0 || WCF::getUser()->isGuest()) {
            throw new PermissionDeniedException();
        }
    }
}

/** @internal */
final class SetLanguageParameters
{
    public function __construct(
        /** @var positive-int */
        public readonly int $languageID,
    ) {}
}
