<?php

namespace wcf\system\endpoint\controller\core\languages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\language\Language;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Enables the language with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/languages/{id:\d+}/enable')]
final class EnableLanguage implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $language = Helper::fetchObjectFromRequestParameter($variables['id'], Language::class);

        $this->assertLanguageCanBeEnabled($language);

        (new \wcf\command\language\EnableLanguage($language))();

        return new JsonResponse([]);
    }

    private function assertLanguageCanBeEnabled(Language $language): void
    {
        WCF::getSession()->checkPermissions(['admin.language.canManageLanguage']);

        if ($language->isDefault) {
            throw new PermissionDeniedException();
        }
    }
}
