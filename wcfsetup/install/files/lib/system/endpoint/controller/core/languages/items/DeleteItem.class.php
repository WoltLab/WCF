<?php

namespace wcf\system\endpoint\controller\core\languages\items;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\language\item\LanguageItem;
use wcf\data\language\item\LanguageItemAction;
use wcf\data\language\item\LanguageItemList;
use wcf\http\Helper;
use wcf\system\endpoint\DeleteRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Deletes the language item with the given ID.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
#[DeleteRequest('/core/languages/items/{id:\d+}')]
final class DeleteItem implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $languageItem = Helper::fetchObjectFromRequestParameter($variables['id'], LanguageItem::class);

        $this->assertLanguageItemCanBeDeleted($languageItem);

        (new LanguageItemAction($this->getLanguageItemsForAllLanguages($languageItem), 'delete'))->executeAction();

        LanguageFactory::getInstance()->clearCache();
        LanguageFactory::getInstance()->deleteLanguageCache();

        return new JsonResponse([]);
    }

    private function assertLanguageItemCanBeDeleted(LanguageItem $option): void
    {
        WCF::getSession()->checkPermissions(['admin.language.canManageLanguage']);

        if (!$option->isCustomLanguageItem) {
            throw new PermissionDeniedException();
        }
    }

    /**
     * @return array<int, LanguageItem>
     */
    private function getLanguageItemsForAllLanguages(LanguageItem $languageItem): array
    {
        $languageItemList = new LanguageItemList();
        $languageItemList->getConditionBuilder()->add('isCustomLanguageItem = ?', [1]);
        $languageItemList->getConditionBuilder()->add('languageItem IN (?)', [
            $languageItem->languageItem
        ]);
        $languageItemList->readObjects();

        return $languageItemList->getObjects();
    }
}
