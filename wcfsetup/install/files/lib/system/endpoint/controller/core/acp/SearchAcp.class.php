<?php

namespace wcf\system\endpoint\controller\core\acp;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;
use wcf\system\endpoint\GetRequest;
use wcf\system\endpoint\IController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\search\acp\ACPSearchHandler;
use wcf\system\WCF;

/**
 * Returns the ACP search results for a given query.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[GetRequest('/core/acp/search')]
final class SearchAcp implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        if (!WCF::getSession()->hasPermission('admin.general.canUseAcp')) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    query: non-empty-string,
                    provider?: string,
                }
                EOT,
        );

        $results = ACPSearchHandler::getInstance()->search(
            $parameters['query'],
            20,
            $parameters['provider'] ?? ''
        );

        $data = [];
        foreach ($results as $resultList) {
            $items = [];
            foreach ($resultList as $item) {
                $items[] = [
                    'link' => $item->getLink(),
                    'subtitle' => $item->getSubtitle(),
                    'title' => $item->getTitle(),
                ];
            }

            foreach ($items as $key => &$item) {
                $double = false;
                foreach ($items as $key2 => $item2) {
                    if ($key !== $key2 && \strcasecmp($item['title'], $item2['title']) === 0) {
                        $double = true;
                        break;
                    }
                }

                if (!$double) {
                    unset($item['subtitle']);
                }
            }
            unset($item);

            $data[] = [
                'items' => $items,
                'title' => $resultList->getTitle(),
            ];
        }

        return new JsonResponse(['results' => $data]);
    }
}
