<?php

namespace wcf\acp\form;

use wcf\acp\page\ArticleListPage;
use wcf\data\article\Article;
use wcf\http\Helper;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\interaction\admin\ArticleInteractions;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\request\LinkHandler;
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
    public string $formAction = 'edit';

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
                if ($contentLanguageID === ($languageID ?? 0)) {
                    return $content->articleContentID;
                }
            } else {
                return $content->articleContentID;
            }
        }

        return null;
    }
}
