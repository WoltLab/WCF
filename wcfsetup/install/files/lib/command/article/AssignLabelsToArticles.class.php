<?php

namespace wcf\command\article;

use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\system\label\LabelHandler;

/**
 * Assigns labels to articles. Existing labels of the given label groups are replaced.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class AssignLabelsToArticles
{
    /**
     * @param list<Article> $articles
     * @param list<int> $labelGroupIDs
     * @param array<int, int|list<int>> $labelIDs
     */
    public function __construct(
        private readonly array $articles,
        private readonly array $labelGroupIDs,
        private readonly array $labelIDs
    ) {}

    public function __invoke(): void
    {
        if ($this->articles === [] || $this->labelGroupIDs === []) {
            return;
        }

        $objectTypeID = LabelHandler::getInstance()->getObjectType('com.woltlab.wcf.article')->objectTypeID;
        $articleIDs = [];
        foreach ($this->articles as $article) {
            $articleIDs[] = $article->articleID;
        }

        foreach ($this->articles as $article) {
            LabelHandler::getInstance()->replaceLabels(
                $this->labelGroupIDs,
                $this->labelIDs,
                'com.woltlab.wcf.article',
                $article->articleID
            );
        }

        // Do not validate permissions for the hasLabels flag.
        $assignedLabels = LabelHandler::getInstance()->getAssignedLabels($objectTypeID, $articleIDs, false);

        // update `hasLabels` flag
        foreach ($this->articles as $article) {
            ArticleBuilder::forUpdate($article)
                ->setHasLabels(($assignedLabels[$article->articleID] ?? []) !== [])
                ->update();
        }
    }
}
