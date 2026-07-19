<?php

namespace wcf\data\article\content;

use wcf\data\article\Article;
use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\attachment\AttachmentHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\tagging\TagEngine;

/**
 * Builder for creating and updating article contents.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<ArticleContent>
 */
final class ArticleContentBuilder extends DatabaseObjectBuilder
{
    /**
     * @var list<string>
     */
    public private(set) array $tags;

    public private(set) AttachmentHandler $attachmentHandler;

    public private(set) HtmlInputProcessor $htmlInputProcessor;

    public function setArticle(Article $article): static
    {
        $this->properties['articleID'] = $article->articleID;

        return $this;
    }

    public function setLanguageID(?int $languageID): static
    {
        $this->properties['languageID'] = $languageID;

        return $this;
    }

    public function setTitle(string $title): static
    {
        $this->properties['title'] = $title;

        return $this;
    }

    public function setSlug(string $slug): static
    {
        $this->properties['slug'] = $slug;

        return $this;
    }

    public function setTeaser(string $teaser): static
    {
        $this->properties['teaser'] = $teaser;

        return $this;
    }

    public function setContent(string $content): static
    {
        $this->properties['content'] = $content;

        return $this;
    }

    public function setImageID(?int $imageID): static
    {
        $this->properties['imageID'] = $imageID;

        return $this;
    }

    public function setTeaserImageID(?int $teaserImageID): static
    {
        $this->properties['teaserImageID'] = $teaserImageID;

        return $this;
    }

    public function setMetaTitle(string $metaTitle): static
    {
        $this->properties['metaTitle'] = $metaTitle;

        return $this;
    }

    public function setMetaDescription(string $metaDescription): static
    {
        $this->properties['metaDescription'] = $metaDescription;

        return $this;
    }

    public function setHasEmbeddedObjects(bool $hasEmbeddedObjects): static
    {
        $this->properties['hasEmbeddedObjects'] = $hasEmbeddedObjects ? 1 : 0;

        return $this;
    }

    public function setAttachments(int $attachments): static
    {
        $this->properties['attachments'] = $attachments;

        return $this;
    }

    /**
     * @param list<string> $tags
     */
    public function setTags(array $tags): static
    {
        $this->tags = $tags;

        return $this;
    }

    public function setAttachmentHandler(AttachmentHandler $attachmentHandler): static
    {
        $this->attachmentHandler = $attachmentHandler;
        $this->properties['attachments'] = \count($attachmentHandler);

        return $this;
    }

    public function setHtmlInputProcessor(HtmlInputProcessor $htmlInputProcessor): static
    {
        $this->htmlInputProcessor = $htmlInputProcessor;
        $this->setContent($this->htmlInputProcessor->getHtml());

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->properties['title'] ?? null;
    }

    public function getTeaser(): ?string
    {
        return $this->properties['teaser'] ?? null;
    }

    public function getContent(): ?string
    {
        return $this->properties['content'] ?? null;
    }

    #[\Override]
    protected function afterCreate(DatabaseObject $object): void
    {
        if (isset($this->htmlInputProcessor)) {
            $this->registerEmbeddedObjects($this->htmlInputProcessor, $object);
        }

        if (isset($this->attachmentHandler)) {
            $this->attachmentHandler->updateObjectID($object->articleContentID);
        }

        if (isset($this->tags)) {
            $this->saveTags($this->tags, $object);
        }
    }

    #[\Override]
    protected function afterUpdate(DatabaseObject $object): void
    {
        if (isset($this->htmlInputProcessor)) {
            $this->registerEmbeddedObjects($this->htmlInputProcessor, $object);
        }

        if (isset($this->tags)) {
            $this->saveTags($this->tags, $object);
        }
    }

    private function registerEmbeddedObjects(HtmlInputProcessor $processor, ArticleContent $articleContent): void
    {
        $processor->setObjectID($articleContent->articleContentID);
        ArticleContentBuilder::forUpdate($articleContent)
            ->setHasEmbeddedObjects(
                MessageEmbeddedObjectManager::getInstance()->registerObjects($processor)
            )
            ->update();
    }

    /**
     * @param list<string> $tags
     */
    private function saveTags(array $tags, ArticleContent $articleContent): void
    {
        $languageID = $articleContent->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID();

        if ($tags === []) {
            TagEngine::getInstance()->deleteObjectTags(
                'com.woltlab.wcf.article',
                $articleContent->articleContentID,
                $languageID
            );
        } else {
            TagEngine::getInstance()->addObjectTags(
                'com.woltlab.wcf.article',
                $articleContent->articleContentID,
                $tags,
                $languageID
            );
        }
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['articleID', 'title'];
    }
}
