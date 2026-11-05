<?php

namespace wcf\action;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\command\article\AssignLabelsToArticles;
use wcf\data\article\Article;
use wcf\data\article\ArticleList;
use wcf\data\label\group\ViewableLabelGroup;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\field\label\LabelFormField;
use wcf\system\form\builder\Psr15DialogForm;
use wcf\system\WCF;

/**
 * Shows the dialog for assigning labels to multiple articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class ArticleBulkAssignLabelDialogAction implements RequestHandlerInterface
{
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                array {
                    ids: non-empty-list<positive-int>
                }
                EOT
        );

        $articleList = new ArticleList();
        $articleList->setObjectIDs($parameters['ids']);
        $articleList->readObjects();
        if ($articleList->getObjects() === []) {
            throw new IllegalLinkException();
        }

        $articles = \array_values(\array_filter(
            $articleList->getObjects(),
            static fn(Article $article): bool => $article->canEdit()
        ));
        if ($articles === []) {
            throw new PermissionDeniedException();
        }

        $labelGroups = $this->getCommonLabelGroups($articles);
        if ($labelGroups === []) {
            throw new IllegalLinkException();
        }

        $form = $this->getForm($labelGroups);

        if ($request->getMethod() === 'GET') {
            return $form->toResponse();
        } elseif ($request->getMethod() === 'POST') {
            $response = $form->validateRequest($request);
            if ($response !== null) {
                return $response;
            }

            new AssignLabelsToArticles(
                $articles,
                \array_keys($labelGroups),
                $form->getData()['labelIDs'] ?? []
            )();

            return new JsonResponse([]);
        } else {
            throw new \LogicException('Unreachable');
        }
    }

    /**
     * Returns the label groups that are available in the categories of all given articles
     * and that the active user is allowed to set.
     *
     * @param list<Article> $articles
     * @return array<int, ViewableLabelGroup>
     */
    private function getCommonLabelGroups(array $articles): array
    {
        $labelGroups = null;
        foreach ($articles as $article) {
            $categoryLabelGroups = $article->getCategory()?->getLabelGroups() ?? [];
            $labelGroups = $labelGroups === null
                ? $categoryLabelGroups
                : \array_intersect_key($labelGroups, $categoryLabelGroups);

            if ($labelGroups === []) {
                return [];
            }
        }

        return \array_filter(
            $labelGroups,
            static fn(ViewableLabelGroup $labelGroup): bool => \count($labelGroup) !== 0
        );
    }

    /**
     * @param array<int, ViewableLabelGroup> $labelGroups
     */
    private function getForm(array $labelGroups): Psr15DialogForm
    {
        $form = new Psr15DialogForm(
            static::class,
            WCF::getLanguage()->get('wcf.article.button.assignLabel')
        );

        foreach ($labelGroups as $labelGroup) {
            $form->appendChild(
                LabelFormField::create('labelIDs' . $labelGroup->groupID)
                    ->objectProperty('labelIDs')
                    ->objectType('com.woltlab.wcf.article')
                    ->required($labelGroup->forceSelection !== 0)
                    ->labelGroup($labelGroup)
            );
        }
        $form->build();

        return $form;
    }
}
