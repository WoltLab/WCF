<?php
namespace wcf\data\article;
use wcf\data\article\category\ArticleCategory;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentAction;
use wcf\data\article\content\ArticleContentEditor;
use wcf\data\language\Language;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\comment\CommentHandler;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\like\LikeHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\version\VersionTracker;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Executes article related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 * 
 * @method	ArticleEditor[]	getObjects()
 * @method	ArticleEditor	getSingleObject()
 */
class ArticleAction extends AbstractDatabaseObjectAction {
	/**
	 * article editor instance
	 * @var ArticleEditor
	 */
	public $articleEditor;
	
	/**
	 * language object
	 * @var Language
	 */
	public $language;
	
	/**
	 * @inheritDoc
	 */
	protected $className = ArticleEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.content.article.canManageArticle'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.content.article.canManageArticle'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.content.article.canManageArticle'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'restore', 'toggleI18n', 'trash', 'update'];
	
	/**
	 * @inheritDoc
	 */
	protected $allowGuestAccess = ['markAllAsRead'];
	
	/**
	 * @inheritDoc
	 * @return	Article
	 */
	public function create() {
		/** @var Article $article */
		$article = parent::create();
		
		// save article content
		if (!empty($this->parameters['content'])) {
			foreach ($this->parameters['content'] as $languageID => $content) {
				if (!empty($content['htmlInputProcessor'])) {
					/** @noinspection PhpUndefinedMethodInspection */
					$content['content'] = $content['htmlInputProcessor']->getHtml();
				}
				
				/** @var ArticleContent $articleContent */
				$articleContent = ArticleContentEditor::create([
					'articleID' => $article->articleID,
					'languageID' => $languageID ?: null,
					'title' => $content['title'],
					'teaser' => $content['teaser'],
					'content' => $content['content'],
					'imageID' => $content['imageID'],
					'teaserImageID' => $content['teaserImageID']
				]);
				$articleContentEditor = new ArticleContentEditor($articleContent);
				
				// save tags
				if (!empty($content['tags'])) {
					TagEngine::getInstance()->addObjectTags('com.woltlab.wcf.article', $articleContent->articleContentID, $content['tags'], ($languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()));
				}
				
				// update search index
				SearchIndexManager::getInstance()->set(
					'com.woltlab.wcf.article',
					$articleContent->articleContentID,
					$articleContent->content,
					$articleContent->title,
					$article->time,
					$article->userID,
					$article->username,
					$languageID ?: null,
					$articleContent->teaser
				);
				
				// save embedded objects
				if (!empty($content['htmlInputProcessor'])) {
					/** @noinspection PhpUndefinedMethodInspection */
					$content['htmlInputProcessor']->setObjectID($articleContent->articleContentID);
					if (MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
						$articleContentEditor->update(['hasEmbeddedObjects' => 1]);
					}
				}
			}
		}
		
		// reset storage
		if (ARTICLE_ENABLE_VISIT_TRACKING) {
			UserStorageHandler::getInstance()->resetAll('unreadArticles');
		}
		
		return $article;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		$isRevert = (!empty($this->parameters['isRevert']));
		
		// update article content
		if (!empty($this->parameters['content'])) {
			foreach ($this->getObjects() as $article) {
				$versionData = [];
				$hasChanges = false;
				
				foreach ($this->parameters['content'] as $languageID => $content) {
					if (!empty($content['htmlInputProcessor'])) {
						/** @noinspection PhpUndefinedMethodInspection */
						$content['content'] = $content['htmlInputProcessor']->getHtml();
					}
					
					$articleContent = ArticleContent::getArticleContent($article->articleID, ($languageID ?: null));
					$articleContentEditor = null;
					if ($articleContent !== null) {
						// update
						$articleContentEditor = new ArticleContentEditor($articleContent);
						$articleContentEditor->update([
							'title' => $content['title'],
							'teaser' => $content['teaser'],
							'content' => $content['content'],
							'imageID' => ($isRevert) ? $articleContent->imageID : $content['imageID'],
							'teaserImageID' => ($isRevert) ? $articleContent->teaserImageID : $content['teaserImageID']
						]);
						
						$versionData[] = $articleContent;
						if ($articleContent->content != $content['content'] || $articleContent->teaser != $content['teaser'] || $articleContent->title != $content['title']) {
							$hasChanges = true;
						}
						
						// delete tags
						if (!$isRevert && empty($content['tags'])) {
							TagEngine::getInstance()->deleteObjectTags('com.woltlab.wcf.article', $articleContent->articleContentID, ($languageID ?: null));
						}
					}
					else {
						/** @var ArticleContent $articleContent */
						$articleContent = ArticleContentEditor::create([
							'articleID' => $article->articleID,
							'languageID' => $languageID ?: null,
							'title' => $content['title'],
							'teaser' => $content['teaser'],
							'content' => $content['content'],
							'imageID' => ($isRevert) ? null : $content['imageID'],
							'teaserImageID' => ($isRevert) ? null : $content['teaserImageID']
						]);
						$articleContentEditor = new ArticleContentEditor($articleContent);
						
						$versionData[] = $articleContent;
						$hasChanges = true;
					}
					
					// save tags
					if (!$isRevert && !empty($content['tags'])) {
						TagEngine::getInstance()->addObjectTags('com.woltlab.wcf.article', $articleContent->articleContentID, $content['tags'], ($languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()));
					}
					
					// update search index
					SearchIndexManager::getInstance()->set(
						'com.woltlab.wcf.article',
						$articleContent->articleContentID,
						$articleContent->content,
						$articleContent->title,
						$article->time,
						$article->userID,
						$article->username, 
						$languageID ?: null,
						$articleContent->teaser
					);
					
					// save embedded objects
					if (!empty($content['htmlInputProcessor'])) {
						/** @noinspection PhpUndefinedMethodInspection */
						$content['htmlInputProcessor']->setObjectID($articleContent->articleContentID);
						if ($articleContent->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
							$articleContentEditor->update(['hasEmbeddedObjects' => $articleContent->hasEmbeddedObjects ? 0 : 1]);
						}
					}
				}
				
				if ($hasChanges) {
					$articleObj = new ArticleVersionTracker($article->getDecoratedObject());
					$articleObj->setContent($versionData);
					VersionTracker::getInstance()->add('com.woltlab.wcf.article', $articleObj);
				}
			}
		}
		
		// reset storage
		if (ARTICLE_ENABLE_VISIT_TRACKING) {
			UserStorageHandler::getInstance()->resetAll('unreadArticles');
		}
	}
	
	/**
	 * Validates parameters to delete articles.
	 *
	 * @throws	UserInputException
	 */
	public function validateDelete() {
		WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if (!$article->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$articleIDs = $articleContentIDs = [];
		foreach ($this->getObjects() as $article) {
			$articleIDs[] = $article->articleID;
			foreach ($article->getArticleContents() as $articleContent) {
				$articleContentIDs[] = $articleContent->articleContentID;
			}
		}
		
		// delete articles
		parent::delete();
		
		if (!empty($articleIDs)) {
			// delete like data
			LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.likeableArticle', $articleIDs);
			// delete comments
			CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.articleComment', $articleContentIDs);
			// delete tag to object entries
			TagEngine::getInstance()->deleteObjects('com.woltlab.wcf.article', $articleContentIDs);
			// delete entry from search index
			SearchIndexManager::getInstance()->delete('com.woltlab.wcf.article', $articleContentIDs);
		}
		
		$this->unmarkItems();
		
		return [
			'objectIDs' => $this->objectIDs,
			'redirectURL' => LinkHandler::getInstance()->getLink('ArticleList', ['isACP' => true])
		];
	}
	
	/**
	 * Validates parameters to move articles to the trash bin.
	 * 
	 * @throws	UserInputException
	 */
	public function validateTrash() {
		WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if ($article->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Moves articles to the trash bin.
	 */
	public function trash() {
		foreach ($this->getObjects() as $articleEditor) {
			$articleEditor->update(['isDeleted' => 1]);
		}
		
		$this->unmarkItems();
		
		// reset storage
		if (ARTICLE_ENABLE_VISIT_TRACKING) {
			UserStorageHandler::getInstance()->resetAll('unreadArticles');
		}
		
		return ['objectIDs' => $this->objectIDs];
	}
	
	/**
	 * Validates parameters to restore articles.
	 * 
	 * @throws	UserInputException
	 */
	public function validateRestore() {
		$this->validateDelete();
	}
	
	/**
	 * Restores articles.
	 */
	public function restore() {
		foreach ($this->getObjects() as $articleEditor) {
			$articleEditor->update(['isDeleted' => 0]);
		}
		
		$this->unmarkItems();
		
		// reset storage
		if (ARTICLE_ENABLE_VISIT_TRACKING) {
			UserStorageHandler::getInstance()->resetAll('unreadArticles');
		}
		
		return ['objectIDs' => $this->objectIDs];
	}
	
	/**
	 * Validates parameters to toggle between i18n and monolingual mode.
	 * 
	 * @throws      UserInputException
	 */
	public function validateToggleI18n() {
		WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);
		
		$this->articleEditor = $this->getSingleObject();
		if ($this->articleEditor->getDecoratedObject()->isMultilingual) {
			$this->readInteger('languageID');
			$this->language = LanguageFactory::getInstance()->getLanguage($this->parameters['languageID']);
			if ($this->language === null) {
				throw new UserInputException('languageID');
			}
			
			$contents = $this->articleEditor->getArticleContents();
			if (!isset($contents[$this->language->languageID])) {
				// there is no content
				throw new UserInputException('languageID');
			}
		}
	}
	
	/**
	 * Toggles between i18n and monolingual mode.
	 */
	public function toggleI18n() {
		$removeContent = [];
		
		// i18n -> monolingual
		if ($this->articleEditor->getDecoratedObject()->isMultilingual) {
			foreach ($this->articleEditor->getArticleContents() as $articleContent) {
				if ($articleContent->languageID == $this->language->languageID) {
					$articleContentEditor = new ArticleContentEditor($articleContent);
					$articleContentEditor->update(['languageID' => null]);
				}
				else {
					$removeContent[] = $articleContent;
				}
			}
		}
		else {
			// monolingual -> i18n
			$articleContent = $this->articleEditor->getArticleContent();
			$data = [];
			foreach (LanguageFactory::getInstance()->getLanguages() as $language) {
				$data[$language->languageID] = [
					'title' => $articleContent->title,
					'teaser' => $articleContent->teaser,
					'content' => $articleContent->content,
					'imageID' => $articleContent->imageID ?: null,
					'teaserImageID' => $articleContent->teaserImageID ?: null
				];
			}
			
			$action = new ArticleAction([$this->articleEditor], 'update', ['content' => $data]);
			$action->executeAction();
			
			$removeContent[] = $articleContent;
		}
		
		if (!empty($removeContent)) {
			$action = new ArticleContentAction($removeContent, 'delete');
			$action->executeAction();
		}
		
		// flush edit history
		VersionTracker::getInstance()->reset('com.woltlab.wcf.article', $this->articleEditor->getDecoratedObject()->articleID);
		
		// update article's i18n state
		$this->articleEditor->update([
			'isMultilingual' => ($this->articleEditor->getDecoratedObject()->isMultilingual) ? 0 : 1
		]);
	}
	
	/**
	 * Marks articles as read.
	 */
	public function markAsRead() {
		if (empty($this->parameters['visitTime'])) {
			$this->parameters['visitTime'] = TIME_NOW;
		}
		
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		foreach ($this->getObjects() as $article) {
			VisitTracker::getInstance()->trackObjectVisit('com.woltlab.wcf.article', $article->articleID, $this->parameters['visitTime']);
		}
		
		// reset storage
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadArticles');
		}
	}
	
	/**
	 * Marks all articles as read.
	 */
	public function markAllAsRead() {
		VisitTracker::getInstance()->trackTypeVisit('com.woltlab.wcf.article');
		
		// reset storage
		if (WCF::getUser()->userID) {
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadArticles');
		}
	}
	
	/**
	 * Validates the mark all as read action.
	 */
	public function validateMarkAllAsRead() {
		// does nothing
	}
	
	/**
	 * Validates the `setCategory` action.
	 * 
	 * @throws	UserInputException
	 */
	public function validateSetCategory() {
		WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);
		
		$this->readBoolean('useMarkedArticles', true);
		
		// if no object ids are given, use clipboard handler
		if (empty($this->objectIDs) && $this->parameters['useMarkedArticles']) {
			$this->objectIDs = array_keys(ClipboardHandler::getInstance()->getMarkedItems(ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.article')));
		}
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		$this->readInteger('categoryID');
		if (ArticleCategory::getCategory($this->parameters['categoryID']) === null) {
			throw new UserInputException('categoryID');
		}
	}
	
	/**
	 * Sets the category of articles.
	 */
	public function setCategory() {
		foreach ($this->getObjects() as $articleEditor) {
			$articleEditor->update(['categoryID' => $this->parameters['categoryID']]);
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Validates the `publish` action.
	 * 
	 * @throws	UserInputException
	 */
	public function validatePublish() {
		WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if ($article->publicationStatus == Article::PUBLISHED) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Publishes articles.
	 */
	public function publish() {
		foreach ($this->getObjects() as $articleEditor) {
			$articleEditor->update([
				'time' => TIME_NOW,
				'publicationStatus' => Article::PUBLISHED,
				'publicationDate' => 0
			]);
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Validates the `unpublish` action.
	 *
	 * @throws	UserInputException
	 */
	public function validateUnpublish() {
		WCF::getSession()->checkPermissions(['admin.content.article.canManageArticle']);
		
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if ($article->publicationStatus != Article::PUBLISHED) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Unpublishes articles.
	 */
	public function unpublish() {
		foreach ($this->getObjects() as $articleEditor) {
			$articleEditor->update(['publicationStatus' => Article::UNPUBLISHED]);
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Unmarks articles.
	 * 
	 * @param	integer[]	$articleIDs
	 */
	protected function unmarkItems(array $articleIDs = []) {
		if (empty($articleIDs)) {
			foreach ($this->getObjects() as $article) {
				$articleIDs[] = $article->articleID;
			}
		}
		
		if (!empty($articleIDs)) {
			ClipboardHandler::getInstance()->unmark($articleIDs, ClipboardHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.article'));
		}
	}
}
