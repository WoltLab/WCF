<?php

namespace wcf\data\article;

use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentBuilder;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\User;
use wcf\system\label\object\ArticleLabelObjectHandler;

/**
 * Builder for creating and updating articles.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<Article>
 */
final class ArticleBuilder extends DatabaseObjectBuilder
{
    /**
     * @var array<int, ArticleContentBuilder>
     */
    public private(set) array $articleContentBuilders = [];

    /**
     * @var list<int>
     */
    public private(set) array $labelIDs;

    public function setUser(User $user): static
    {
        $this->properties['userID'] = $user->userID;
        $this->properties['username'] = $user->username;

        return $this;
    }

    public function setUsername(string $username): static
    {
        $this->properties['username'] = $username;

        return $this;
    }

    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    public function setCategory(ArticleCategory $category): static
    {
        $this->properties['categoryID'] = $category->categoryID;

        return $this;
    }

    public function setIsMultilingual(bool $isMultilingual): static
    {
        $this->properties['isMultilingual'] = $isMultilingual ? 1 : 0;

        return $this;
    }

    public function setPublicationStatus(int $publicationStatus): static
    {
        $this->properties['publicationStatus'] = $publicationStatus;

        return $this;
    }

    public function setPublicationDate(int $publicationDate): static
    {
        $this->properties['publicationDate'] = $publicationDate;

        return $this;
    }

    public function setEnableComments(bool $enableComments): static
    {
        $this->properties['enableComments'] = $enableComments ? 1 : 0;

        return $this;
    }

    public function setIsDeleted(bool $isDeleted): static
    {
        $this->properties['isDeleted'] = $isDeleted ? 1 : 0;

        return $this;
    }

    public function setHasLabels(bool $hasLabels): static
    {
        $this->properties['hasLabels'] = $hasLabels ? 1 : 0;

        return $this;
    }

    public function incrementViews(int $views): static
    {
        $this->incrementProperties['views'] = $views;

        return $this;
    }

    public function incrementReactions(int $reactions): static
    {
        $this->incrementProperties['cumulativeLikes'] = $reactions;

        return $this;
    }

    /**
     * Sets the complete list of label ids that will be assigned to the article.
     *
     * The labels are always saved without validating permissions, therefore this
     * must be the full set of labels including the labels of label groups that the
     * active user is not allowed to set. A partial update is not supported; any
     * previously assigned label that is not part of `$labelIDs` will be removed.
     *
     * @param int[] $labelIDs
     */
    public function setLabelIDs(array $labelIDs): static
    {
        $this->labelIDs = $labelIDs;
        $this->properties['hasLabels'] = $labelIDs !== [] ? 1 : 0;

        return $this;
    }

    public function setLabelID(int $labelID): static
    {
        if (isset($this->labelIDs)) {
            $this->labelIDs[] = $labelID;
            $this->properties['hasLabels'] = 1;
        } else {
            $this->setLabelIDs([$labelID]);
        }

        return $this;
    }

    /**
     * Returns the content builder for the given language, creating it on demand.
     * Pass `null` for the monolingual content.
     */
    public function getArticleContentBuilder(?int $languageID): ArticleContentBuilder
    {
        if (!isset($this->articleContentBuilders[$languageID ?: 0])) {
            $existingContent = $this->object !== null
                ? ArticleContent::getArticleContent($this->object->getObjectID(), $languageID)
                : null;

            if ($existingContent !== null) {
                $this->articleContentBuilders[$languageID ?: 0] = ArticleContentBuilder::forUpdate($existingContent);
            } else {
                $this->articleContentBuilders[$languageID ?: 0] = ArticleContentBuilder::forCreate()
                    ->setLanguageID($languageID);
            }
        }

        return $this->articleContentBuilders[$languageID ?: 0];
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        foreach ($this->articleContentBuilders as $articleContentBuilder) {
            $articleContentBuilder
                ->setArticle($object)
                ->create();
        }

        if (isset($this->labelIDs)) {
            ArticleLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $object->articleID, false);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        foreach ($this->articleContentBuilders as $articleContentBuilder) {
            $articleContentBuilder->setArticle($object);
            if ($articleContentBuilder->isUpdate()) {
                $articleContentBuilder->update();
            } else {
                $articleContentBuilder->create();
            }
        }

        if (isset($this->labelIDs)) {
            ArticleLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $object->articleID, false);
        }
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['userID', 'username', 'time', 'categoryID'];
    }
}
