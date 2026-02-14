<?php

namespace wcf\acp\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\article\SetArticleCategory;
use wcf\data\article\ArticleList;
use wcf\data\article\category\ArticleCategory;
use wcf\data\category\CategoryNodeTree;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Handles setting the category for articles.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ArticleCategoryAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!WCF::getSession()->getPermission("admin.content.article.canManageArticle")) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    ids: positive-int[]
                }
                EOT
        );

        if ($parameters['ids'] === []) {
            throw new IllegalLinkException();
        }

        $articleList = new ArticleList();
        $articleList->setObjectIDs($parameters['objectIDs']);
        $articleList->readObjects();

        $form = $this->getForm();

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            $data = $form->getData()['data'];
            $category = ArticleCategory::getCategory($data['categoryID']);

            WCF::getDB()->beginTransaction();

            foreach ($articleList as $article) {
                (new SetArticleCategory($article, $category))();
            }

            WCF::getDB()->commitTransaction();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    private function getForm(): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.article.button.setCategory')
        );
        $form->appendChildren([
            SingleSelectionFormField::create('categoryID')
                ->label('wcf.global.category')
                ->options((new CategoryNodeTree('com.woltlab.wcf.article.category'))->getIterator(), true)
                ->required()
        ]);

        $form->markRequiredFields(false);
        $form->build();

        return $form;
    }
}
