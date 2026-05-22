<?php

namespace wcf\system\endpoint\controller\core\categories;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\data\category\CategoryList;
use wcf\http\Helper;
use wcf\system\category\CategoryHandler;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;

/**
 * Sets the positions of the categories of an object type.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
#[PostRequest('/core/categories/object-types/{id:\d+}/positions')]
final class SetCategoryPositions implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $objectType = CategoryHandler::getInstance()->getObjectType(\intval($variables['id']));
        if ($objectType === null) {
            throw new UserInputException('objectTypeID');
        }

        if (!$objectType->getProcessor()->canEditCategory()) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapApiParameters($request, SetCategoryPositionsParameters::class);
        $positions = $this->validatePositions($objectType->objectTypeID, $parameters->positions);

        (new \wcf\command\category\SetCategoryPositions($positions))();

        return new JsonResponse([]);
    }

    /**
     * @param array<int, list<int>> $positions
     * @return array<int, list<int>>
     */
    private function validatePositions(int $objectTypeID, array $positions): array
    {
        $categoryIDs = [];
        foreach ($positions as $children) {
            $categoryIDs = \array_merge($categoryIDs, $children);
        }

        if ($categoryIDs === []) {
            return $positions;
        }

        $categoryList = new CategoryList();
        $categoryList->getConditionBuilder()->add('category.categoryID IN (?)', [$categoryIDs]);
        $categoryList->getConditionBuilder()->add('category.objectTypeID = ?', [$objectTypeID]);
        $categoryList->readObjects();
        $categories = $categoryList->getObjects();

        if (\count($categories) !== \count($categoryIDs)) {
            throw new IllegalLinkException();
        }

        foreach ($positions as $parentCategoryID => $children) {
            if ($parentCategoryID && !isset($categories[$parentCategoryID])) {
                throw new IllegalLinkException();
            }
        }

        $parentOf = [];
        foreach ($positions as $parentCategoryID => $children) {
            foreach ($children as $childID) {
                if (isset($parentOf[$childID])) {
                    throw new IllegalLinkException();
                }
                $parentOf[$childID] = $parentCategoryID;
            }
        }

        foreach (\array_keys($parentOf) as $startID) {
            $current = $startID;
            $seen = [$startID => true];
            while (!empty($parentOf[$current])) {
                $current = $parentOf[$current];
                if (isset($seen[$current])) {
                    throw new IllegalLinkException();
                }
                $seen[$current] = true;
            }
        }

        return $positions;
    }
}

/** @internal */
final class SetCategoryPositionsParameters
{
    public function __construct(
        /** @var array<int, list<positive-int>> */
        public readonly array $positions,
    ) {}
}
