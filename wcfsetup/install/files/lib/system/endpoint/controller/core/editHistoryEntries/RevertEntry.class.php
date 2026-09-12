<?php

namespace wcf\system\endpoint\controller\core\editHistoryEntries;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\object\type\ObjectTypeCache;
use wcf\http\Helper;
use wcf\system\edit\IHistorySavingObjectTypeProvider;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;

/**
 * Reverts an object to the version stored in the given edit history entry.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/edit-history-entries/{id:\d+}/revert')]
final class RevertEntry implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (\MODULE_EDIT_HISTORY === 0) {
            throw new IllegalLinkException();
        }

        $entry = Helper::fetchObjectFromRequestParameter($variables['id'], EditHistoryEntry::class);

        $objectType = ObjectTypeCache::getInstance()->getObjectType($entry->objectTypeID);
        $processor = $objectType->getProcessor();
        \assert($processor instanceof IHistorySavingObjectTypeProvider);

        $object = $entry->getObject();
        $processor->checkPermissions($object);

        $object->revertVersion($entry);

        return new JsonResponse([]);
    }
}
