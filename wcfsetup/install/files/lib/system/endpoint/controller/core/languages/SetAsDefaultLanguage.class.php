<?php

namespace wcf\system\endpoint\controller\core\languages;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\http\Helper;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Flags the language with the given ID as default language.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[PostRequest('/core/languages/{id:\d+}/default')]
final class SetAsDefaultLanguage implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $language = Helper::fetchObjectFromRequestParameter($variables['id'], Language::class);

        $this->assertLanguageCanBeSetAsDefault($language);

        $languageEditor = new LanguageEditor($language);
        $languageEditor->setAsDefault();

        return new JsonResponse([]);
    }

    private function assertLanguageCanBeSetAsDefault(Language $language): void
    {
        WCF::getSession()->checkPermissions(['admin.language.canManageLanguage']);

        if ($language->isDefault) {
            throw new PermissionDeniedException();
        }
    }
}
