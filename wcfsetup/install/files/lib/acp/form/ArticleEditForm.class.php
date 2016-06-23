<?php
namespace wcf\acp\form;
use wcf\data\article\Article;
use wcf\data\article\ArticleAction;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\LanguageFactory;
use wcf\system\tagging\TagEngine;
use wcf\system\WCF;
use wcf\util\DateUtil;

/**
 * Shows the article edit form.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Form
 * @since	3.0
 */
class ArticleEditForm extends ArticleAddForm {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.article.list';
	
	/**
	 * article id
	 * @var	integer
	 */
	public $articleID = 0;
	
	/**
	 * article object
	 * @var	Article
	 */
	public $article = null;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->articleID = intval($_REQUEST['id']);
		$this->article = new Article($this->articleID);
		if (!$this->article->articleID) {
			throw new IllegalLinkException();
		}
		if ($this->article->isMultilingual) $this->isMultilingual = 1;
	}
	
	/**
	 * @inheritDoc
	 */
	protected function readMultilingualSetting() {
		// not required for editing
	}
	
	/**
	 * @inheritDoc
	 */
	public function save() {
		AbstractForm::save();
		
		$content = [];
		if ($this->isMultilingual) {
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$content[$language->languageID] = [
					'title' => (!empty($this->title[$language->languageID]) ? $this->title[$language->languageID] : ''),
					'tags' => (!empty($this->tags[$language->languageID]) ? $this->tags[$language->languageID] : []),
					'teaser' => (!empty($this->teaser[$language->languageID]) ? $this->teaser[$language->languageID] : ''),
					'content' => (!empty($this->content[$language->languageID]) ? $this->content[$language->languageID] : ''),
					'htmlInputProcessor' => (isset($this->htmlInputProcessors[$language->languageID]) ? $this->htmlInputProcessors[$language->languageID] : null),
					'imageID' => (!empty($this->imageID[$language->languageID]) ? $this->imageID[$language->languageID] : null)
				];
			}
		}
		else {
			$content[0] = [
				'title' => (!empty($this->title[0]) ? $this->title[0] : ''),
				'tags' => (!empty($this->tags[0]) ? $this->tags[0] : []),
				'teaser' => (!empty($this->teaser[0]) ? $this->teaser[0] : ''),
				'content' => (!empty($this->content[0]) ? $this->content[0] : ''),
				'htmlInputProcessor' => (isset($this->htmlInputProcessors[0]) ? $this->htmlInputProcessors[0] : null),
				'imageID' => (!empty($this->imageID[0]) ? $this->imageID[0] : null)
			];
		}
		
		$data = [
			'categoryID' => $this->categoryID,
			'publicationStatus' => $this->publicationStatus,
			'publicationDate' => ($this->publicationStatus == Article::DELAYED_PUBLICATION ? $this->publicationDateObj->getTimestamp() : 0),
			'enableComments' => $this->enableComments,
			'userID' => $this->author->userID,
			'username' => $this->author->username,
			'time' => $this->timeObj->getTimestamp()
		];
		
		$this->objectAction = new ArticleAction([$this->article], 'update', ['data' => array_merge($this->additionalFields, $data), 'content' => $content]);
		$this->objectAction->executeAction();
		
		// call saved event
		$this->saved();
		
		// show success
		WCF::getTPL()->assign('success', true);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		if (!empty($_POST) && !WCF::getSession()->getPermission('admin.content.cms.canUseMedia')) {
			foreach ($this->article->getArticleContents() as $languageID => $content) {
				$this->imageID[$languageID] = $content['imageID'];
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
				
				// get tags
				if (MODULE_TAGGING) {
					$this->tags[$languageID] = TagEngine::getInstance()->getObjectTags(
						'com.woltlab.wcf.article',
						$content->articleContentID,
						[($languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID())]
					);
				}
			}
			
			$this->readImages();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => 'edit',
			'articleID' => $this->articleID,
			'article' => $this->article
		]);
	}
}
