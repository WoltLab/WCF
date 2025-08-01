<?php

namespace wcf\system\endpoint\controller\core\languages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\language\Language;
use wcf\data\language\LanguageAction;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Deletes the language with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/languages/{id:\d+}')]
final class DeleteLanguage implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $language = Helper::fetchObjectFromRequestParameter($variables['id'], Language::class);

        $this->assertLanguageCanBeDeleted($language);

        (new LanguageAction([$language], 'delete'))->executeAction();

        LanguageFactory::getInstance()->clearCache();
        LanguageFactory::getInstance()->deleteLanguageCache();

        return new JsonResponse([]);
    }

    private function assertLanguageCanBeDeleted(Language $language): void
    {
        WCF::getSession()->checkPermissions(['admin.language.canManageLanguage']);

        if (!$language->isDeletable()) {
            throw new PermissionDeniedException();
        }
    }
}
