<?php

namespace wcf\command\article;

use wcf\command\attachment\CopyAttachments;
use wcf\data\article\Article;
use wcf\data\article\ArticleBuilder;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentBuilder;
use wcf\event\article\ArticleCopied;
use wcf\system\event\EventHandler;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\label\object\ArticleLabelObjectHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;

/**
 * Creates an unpublished copy of an existing article.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class CopyArticle
{
    public function __construct(
        private readonly Article $article,
    ) {}

    public function __invoke(): Article
    {
        $category = $this->article->getCategory();
        \assert($category !== null);

        $builder = ArticleBuilder::forCreate()
            ->setUserID($this->article->userID)
            ->setUsername($this->article->username)
            ->setTime(\TIME_NOW)
            ->setCategory($category)
            ->setIsMultilingual($this->article->isMultilingual !== 0)
            ->setPublicationStatus(Article::UNPUBLISHED)
            ->setPublicationDate(0)
            ->setEnableComments($this->article->enableComments !== 0)
            ->setLabelIDs($this->getLabelIDs());

        foreach ($this->article->getArticleContents() as $content) {
            $builder->getArticleContentBuilder($content->languageID)
                ->setTitle($this->getTitle($content))
                ->setTeaser($content->teaser ?? '')
                ->setContent($content->content ?? '')
                ->setImageID($content->imageID ?: null)
                ->setTeaserImageID($content->teaserImageID ?: null)
                ->setMetaTitle($content->metaTitle)
                ->setMetaDescription($content->metaDescription)
                ->setTags($this->getTags($content));
        }

        $newArticle = new CreateArticle($builder)();

        $sourceContents = [];
        foreach ($this->article->getArticleContents() as $content) {
            $sourceContents[$content->languageID ?: 0] = $content;
        }

        foreach ($newArticle->getArticleContents() as $newContent) {
            $sourceContent = $sourceContents[$newContent->languageID ?: 0] ?? null;
            if ($sourceContent !== null) {
                $this->copyEmbeddedContent($sourceContent, $newContent);
            }
        }

        EventHandler::getInstance()->fire(
            new ArticleCopied($this->article, $newArticle)
        );

        return $newArticle;
    }

    /**
     * @return list<int>
     */
    private function getLabelIDs(): array
    {
        $labels = ArticleLabelObjectHandler::getInstance()->getAssignedLabels(
            [$this->article->articleID],
            false
        );

        return \array_keys($labels[$this->article->articleID] ?? []);
    }

    private function getTitle(ArticleContent $content): string
    {
        $language = $content->languageID !== null
            ? LanguageFactory::getInstance()->getLanguage($content->languageID)
            : null;

        $title = ($language ?? WCF::getLanguage())->getDynamicVariable(
            'wcf.acp.article.copy.title',
            ['title' => $content->title]
        );

        return \mb_substr($title, 0, 255);
    }

    /**
     * @return list<string>
     */
    private function getTags(ArticleContent $content): array
    {
        $tags = TagEngine::getInstance()->getObjectTags(
            'com.woltlab.wcf.article',
            $content->articleContentID,
            [$content->languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
        );

        return \array_values(\array_map(static fn($tag) => $tag->name, $tags));
    }

    /**
     * Copies the attachments of the source content, updates the references
     * to them in the content and registers the embedded objects.
     */
    private function copyEmbeddedContent(ArticleContent $sourceContent, ArticleContent $newContent): void
    {
        $attachmentIDs = new CopyAttachments(
            'com.woltlab.wcf.article.content',
            $sourceContent->articleContentID,
            'com.woltlab.wcf.article.content',
            $newContent->articleContentID
        )();

        $htmlInputProcessor = new HtmlInputProcessor();
        $html = $newContent->content ?? '';
        if ($attachmentIDs !== []) {
            $html = $this->replaceAttachmentIDs($htmlInputProcessor, $html, $attachmentIDs);
        }

        $htmlInputProcessor->processEmbeddedContent(
            $html,
            'com.woltlab.wcf.article.content',
            $newContent->articleContentID
        );

        ArticleContentBuilder::forUpdate($newContent)
            ->setContent($html)
            ->setAttachments(\count($attachmentIDs))
            ->setHasEmbeddedObjects(
                MessageEmbeddedObjectManager::getInstance()->registerObjects($htmlInputProcessor)
            )
            ->update();
    }

    /**
     * @param array<int, int> $attachmentIDs
     */
    private function replaceAttachmentIDs(HtmlInputProcessor $htmlInputProcessor, string $html, array $attachmentIDs): string
    {
        $htmlInputProcessor->processIntermediate($html);
        $nodeProcessor = $htmlInputProcessor->getHtmlInputNodeProcessor();

        foreach ($nodeProcessor->getDocument()->getElementsByTagName('woltlab-metacode') as $element) {
            if ($element->getAttribute('data-name') !== 'attach') {
                continue;
            }

            $attributes = $nodeProcessor->parseAttributes($element->getAttribute('data-attributes'));
            $attachmentID = (int)($attributes[0] ?? 0);
            if (!isset($attachmentIDs[$attachmentID])) {
                continue;
            }

            $attributes[0] = $attachmentIDs[$attachmentID];
            $element->setAttribute(
                'data-attributes',
                \base64_encode(\json_encode($attributes, \JSON_THROW_ON_ERROR))
            );
        }

        return $htmlInputProcessor->getHtml();
    }
}
