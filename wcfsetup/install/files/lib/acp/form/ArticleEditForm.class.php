<?php

namespace wcf\acp\form;

use wcf\acp\page\ArticleListPage;
use wcf\data\article\Article;
use wcf\data\IStorableObject;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\IFormDocument;
use wcf\system\interaction\admin\ArticleInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\label\object\ArticleLabelObjectHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\tagging\TagEngine;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;

/**
 * Shows the article edit form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ArticleEditForm extends ArticleAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.list';

    /**
     * @inheritDoc
     */
    public $formAction = 'edit';

    #[\Override]
    public function readParameters(): void
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(Article::class);

        if ($this->formObject->isMultilingual) {
            $this->isMultilingual = 1;
        }

        if (!$this->formObject->canEdit()) {
            throw new PermissionDeniedException();
        }
    }

    #[\Override]
    protected function readMultilingualSetting(): void
    {
        // not required for editing
    }

    #[\Override]
    public function save(): void
    {
        if (
            !WCF::getSession()->hasPermission('admin.content.article.canManageArticle')
            && !WCF::getSession()->hasPermission('admin.content.article.canManageOwnArticles')
        ) {
            $this->additionalFields['publicationStatus'] = $this->formObject->publicationStatus;
            $this->additionalFields['publicationDate'] = $this->formObject->publicationDate;
        }

        AbstractFormBuilderForm::save();

        // save labels
        $labelIDs = $this->objectAction->getParameters()['labelIDs'] ?? [];
        ArticleLabelObjectHandler::getInstance()->setLabels($labelIDs, $this->formObject->articleID);
    }

    #[\Override]
    public function finalizeForm(): void
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'editArticleProcessor',
                    // Save callback: preserve images when user can't use media
                    function (IFormDocument $document, array $parameters) {
                        if (!WCF::getSession()->hasPermission('admin.content.cms.canUseMedia')) {
                            foreach ($this->formObject->getArticleContents() as $languageID => $content) {
                                $key = $this->isMultilingual ? $languageID : 0;
                                if (isset($parameters['content'][$key])) {
                                    $parameters['content'][$key]['imageID'] = $content->imageID;
                                    $parameters['content'][$key]['teaserImageID'] = $content->teaserImageID;
                                }
                            }
                        }

                        return $parameters;
                    },
                    // Object callback: load article data for editing
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof Article);

                        if ($object->publicationDate === 0) {
                            unset($data['publicationDate']);
                        }

                        foreach ($object->getArticleContents() as $languageID => $content) {
                            if ($this->isMultilingual) {
                                $language = LanguageFactory::getInstance()->getLanguage($languageID);
                                if ($language === null) {
                                    continue;
                                }
                                $lc = $language->languageCode;

                                $data["title_{$lc}"] = $content->title;
                                $data["teaser_{$lc}"] = $content->teaser;
                                $data["content_{$lc}"] = $content->content;
                                $data["imageID_{$lc}"] = $content->imageID;
                                $data["teaserImageID_{$lc}"] = $content->teaserImageID;
                                $data["metaTitle_{$lc}"] = $content->metaTitle;
                                $data["metaDescription_{$lc}"] = $content->metaDescription;

                                if (\MODULE_TAGGING) {
                                    $data["tags_{$lc}"] = TagEngine::getInstance()->getObjectTags(
                                        'com.woltlab.wcf.article',
                                        $content->articleContentID,
                                        [$languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
                                    );
                                }
                            } else {
                                $data['title'] = $content->title;
                                $data['teaser'] = $content->teaser;
                                $data['content'] = $content->content;
                                $data['imageID'] = $content->imageID;
                                $data['teaserImageID'] = $content->teaserImageID;
                                $data['metaTitle'] = $content->metaTitle;
                                $data['metaDescription'] = $content->metaDescription;

                                if (\MODULE_TAGGING) {
                                    $data['tags'] = TagEngine::getInstance()->getObjectTags(
                                        'com.woltlab.wcf.article',
                                        $content->articleContentID,
                                    );
                                }
                            }
                        }

                        return $data;
                    }
                )
            );
    }

    #[\Override]
    public function assignVariables(): void
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'articleID' => $this->formObject->articleID,
            'article' => $this->formObject,
            'lastVersion' => VersionTracker::getInstance()->getLastVersion(
                'com.woltlab.wcf.article',
                $this->formObject->articleID
            ),
            'interactionContextMenu' => StandaloneInteractionContextMenuComponent::forContentHeaderButton(
                new ArticleInteractions(),
                $this->formObject,
                LinkHandler::getInstance()->getControllerLink(ArticleListPage::class)
            ),
        ]);
    }

    #[\Override]
    protected function getAttachmentObjectID(?int $languageID = null): ?int
    {
        foreach ($this->formObject->getArticleContents() as $contentLanguageID => $content) {
            if ($this->isMultilingual) {
                if ($contentLanguageID == $languageID) {
                    return $content->articleContentID;
                }
            } else {
                return $content->articleContentID;
            }
        }

        return null;
    }
}
