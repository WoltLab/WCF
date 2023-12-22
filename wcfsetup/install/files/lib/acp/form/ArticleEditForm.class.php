<?php

namespace wcf\acp\form;

use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\label\object\ArticleLabelObjectHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\tagging\TagEngine;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows the article edit form.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
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
    public $neededPermissions = [];

    /**
     * article id
     * @var int
     */
    public $articleID = 0;

    /**
     * article object
     * @var Article
     */
    public $article;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->articleID = \intval($_REQUEST['id']);
        }
        $this->article = new Article($this->articleID);
        if (!$this->article->articleID) {
            throw new IllegalLinkException();
        }
        if ($this->article->isMultilingual) {
            $this->isMultilingual = 1;
        }

        if (!$this->article->canEdit()) {
            throw new PermissionDeniedException();
        }

        $this->attachmentObjectID = $this->article->articleID;
    }

    /**
     * @inheritDoc
     */
    protected function readMultilingualSetting()
    {
        // not required for editing
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        // save labels
        ArticleLabelObjectHandler::getInstance()->setLabels($this->labelIDs, $this->article->articleID);
        $labelIDs = ArticleLabelObjectHandler::getInstance()->getAssignedLabels([$this->article->articleID], false);

        $content = [];
        if ($this->isMultilingual) {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $content[$language->languageID] = [
                    'title' => !empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : '',
                    'tags' => !empty($this->tags[$language->languageID]) ? $this->tags[$language->languageID] : [],
                    'teaser' => !empty($this->teaser[$language->languageID]) ? $this->teaser[$language->languageID] : '',
                    'content' => !empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : '',
                    'htmlInputProcessor' => $this->htmlInputProcessors[$language->languageID] ?? null,
                    'imageID' => !empty($this->imageID[$language->languageID]) ? $this->imageID[$language->languageID] : null,
                    'teaserImageID' => !empty($this->teaserImageID[$language->languageID]) ? $this->teaserImageID[$language->languageID] : null,
                    'metaTitle' => !empty($this->metaTitle[$language->languageID]) ? $this->metaTitle[$language->languageID] : '',
                    'metaDescription' => !empty($this->metaDescription[$language->languageID]) ? $this->metaDescription[$language->languageID] : '',
                ];
            }
        } else {
            $content[0] = [
                'title' => !empty($this->title[0]) ? $this->title[0] : '',
                'tags' => !empty($this->tags[0]) ? $this->tags[0] : [],
                'teaser' => !empty($this->teaser[0]) ? $this->teaser[0] : '',
                'content' => !empty($this->content[0]) ? $this->content[0] : '',
                'htmlInputProcessor' => $this->htmlInputProcessors[0] ?? null,
                'imageID' => !empty($this->imageID[0]) ? $this->imageID[0] : null,
                'teaserImageID' => !empty($this->teaserImageID[0]) ? $this->teaserImageID[0] : null,
                'metaTitle' => !empty($this->metaTitle[0]) ? $this->metaTitle[0] : '',
                'metaDescription' => !empty($this->metaDescription[0]) ? $this->metaDescription[0] : '',
            ];
        }

        $data = [
            'categoryID' => $this->categoryID,
            'publicationStatus' => $this->publicationStatus,
            'publicationDate' => $this->publicationStatus == Article::DELAYED_PUBLICATION ? $this->publicationDateObj->getTimestamp() : 0,
            'enableComments' => $this->enableComments,
            'userID' => $this->author->userID,
            'username' => $this->author->username,
            'time' => $this->timeObj->getTimestamp(),
            'hasLabels' => (isset($labelIDs[$this->article->articleID]) && !empty($labelIDs[$this->article->articleID])) ? 1 : 0,
        ];

        $this->objectAction = new ArticleAction(
            [$this->article],
            'update',
            [
                'data' => \array_merge($this->additionalFields, $data),
                'content' => $content,
                'attachmentHandler' => $this->attachmentHandler,
            ]
        );
        $this->objectAction->executeAction();

        // call saved event
        $this->saved();

        // Ensure that the CKEditor has the correct content after save.
        if ($this->isMultilingual) {
            foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
                $this->content[$language->languageID] = isset($this->htmlInputProcessors[$language->languageID]) ?
                    $this->htmlInputProcessors[$language->languageID]->getHtml() : '';
            }
        } else {
            $this->content[0] = isset($this->htmlInputProcessors[0]) ? $this->htmlInputProcessors[0]->getHtml() : '';
        }

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        if (!empty($_POST) && !WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
            foreach ($this->article->getArticleContents() as $languageID => $content) {
                $this->imageID[$languageID] = $content->imageID;
                $this->teaserImageID[$languageID] = $content->teaserImageID;
            }

            $this->readImages();
        }

        parent::readData();

        if (empty($_POST)) {
            $this->categoryID = $this->article->categoryID;
            $this->publicationStatus = $this->article->publicationStatus;
            $this->enableComments = $this->article->enableComments;
            $this->username = $this->article->username;
            $dateTime = DateUtil::getDateTimeByTimestamp($this->article->time);
            $dateTime->setTimezone(WCF::getUser()->getTimeZone());
            $this->time = $dateTime->format('c');
            if ($this->article->publicationDate) {
                $dateTime = DateUtil::getDateTimeByTimestamp($this->article->publicationDate);
                $dateTime->setTimezone(WCF::getUser()->getTimeZone());
                $this->publicationDate = $dateTime->format('c');
            }

            foreach ($this->article->getArticleContents() as $languageID => $content) {
                $this->title[$languageID] = $content->title;
                $this->teaser[$languageID] = $content->teaser;
                $this->content[$languageID] = $content->content;
                $this->imageID[$languageID] = $content->imageID;
                $this->teaserImageID[$languageID] = $content->teaserImageID;
                $this->metaTitle[$languageID] = $content->metaTitle;
                $this->metaDescription[$languageID] = $content->metaDescription;

                // get tags
                if (MODULE_TAGGING) {
                    $this->tags[$languageID] = TagEngine::getInstance()->getObjectTags(
                        'com.woltlab.wcf.article',
                        $content->articleContentID,
                        [$languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()]
                    );
                }
            }

            $this->readImages();

            // labels
            $assignedLabels = ArticleLabelObjectHandler::getInstance()->getAssignedLabels(
                [$this->article->articleID],
                true
            );
            if (isset($assignedLabels[$this->article->articleID])) {
                foreach ($assignedLabels[$this->article->articleID] as $label) {
                    $this->labelIDs[$label->groupID] = $label->labelID;
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'action' => 'edit',
            'articleID' => $this->articleID,
            'article' => $this->article,
            'defaultLanguageID' => LanguageFactory::getInstance()->getDefaultLanguageID(),
            'languages' => LanguageFactory::getInstance()->getLanguages(),
            'lastVersion' => VersionTracker::getInstance()->getLastVersion('com.woltlab.wcf.article', $this->articleID),
        ]);
    }
}
