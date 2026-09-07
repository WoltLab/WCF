<?php

namespace wcf\system\showOrder;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use wcf\http\Helper;

/**
 * Handles the change of the show order of elements.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ShowOrderHandler
{
    /**
     * @var list<ShowOrderItem>
     */
    private readonly array $items;

    /**
     * @param ShowOrderItem[] $items
     */
    public function __construct(array $items)
    {
        $this->items = \array_values($items);
    }

    public function toJsonResponse(): JsonResponse
    {
        return new JsonResponse($this->items);
    }

    /**
     * @return list<ShowOrderItem>
     */
    public function getSortedItemsFromRequest(ServerRequestInterface $request): array
    {
        $result = Helper::mapRequestBody(
            $request->getParsedBody(),
            <<<'VALUES'
                array{
                    values: list<positive-int|numeric-string>
                }
                VALUES,
        );

        // The ids must be normalized before the deduplication, because a value that is
        // submitted both as an int and as a string would otherwise survive twice.
        $values = \array_unique(\array_map(\intval(...), $result['values']));

        $items = \array_filter(\array_map(function (int $value) {
            return \array_find($this->items, static fn(ShowOrderItem $item) => $item->id === $value);
        }, $values));

        // The callers rely on the result being a list, because they iterate it by position.
        return \array_values($items);
    }
}
