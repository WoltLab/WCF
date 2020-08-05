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
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\language\LanguageFactory;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\reaction\ReactionHandler;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;
use wcf\system\user\activity\event\UserActivityEventHandler;
use wcf\system\user\notification\object\ArticleUserNotificationObject;
use wcf\system\user\notification\UserNotificationHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\user\storage\UserStorageHandler;
use wcf\system\version\VersionTracker;
use wcf\system\visitTracker\VisitTracker;
use wcf\system\WCF;

/**
 * Executes article related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
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
	protected $requireACP = ['create', 'update'];
	
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
			UserStorageHandler::getInstance()->resetAll('unreadWatchedArticles');
			UserStorageHandler::getInstance()->resetAll('unreadArticlesByCategory');
		}
		
		if ($article->publicationStatus == Article::PUBLISHED) {
			ArticleEditor::updateArticleCounter([$article->userID => 1]);
			
			UserObjectWatchHandler::getInstance()->updateObject(
				'com.woltlab.wcf.article.category',
				$article->getCategory()->categoryID,
				'article',
				'com.woltlab.wcf.article.notification',
				new ArticleUserNotificationObject($article)
			);
			
			UserActivityEventHandler::getInstance()->fireEvent('com.woltlab.wcf.article.recentActivityEvent', $article->articleID, null, $article->userID, $article->time);
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
						isset($content['content']) ? $content['content'] : $articleContent->content,
						isset($content['title']) ? $content['title'] : $articleContent->title,
						$article->time,
						$article->userID,
						$article->username, 
						$languageID ?: null,
						isset($content['teaser']) ? $content['teaser'] : $articleContent->teaser
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
			UserStorageHandler::getInstance()->resetAll('unreadWatchedArticles');
			UserStorageHandler::getInstance()->resetAll('unreadArticlesByCategory');
		}
		
		$publicationStatus = (isset($this->parameters['data']['publicationStatus'])) ? $this->parameters['data']['publicationStatus'] : null;
		if ($publicationStatus !== null) {
			$usersToArticles = $resetArticleIDs = [];
			/** @var ArticleEditor $articleEditor */
			foreach ($this->objects as $articleEditor) {
				if ($publicationStatus != $articleEditor->publicationStatus) {
					// The article was published before or was now published.
					if ($publicationStatus == Article::PUBLISHED || $articleEditor->publicationStatus == Article::PUBLISHED) {
						if (!isset($usersToArticles[$articleEditor->userID])) {
							$usersToArticles[$articleEditor->userID] = 0;
						}
						
						$usersToArticles[$articleEditor->userID] += ($publicationStatus == Article::PUBLISHED) ? 1 : -1;
					}
					
					if ($publicationStatus == Article::PUBLISHED) {
						UserObjectWatchHandler::getInstance()->updateObject(
							'com.woltlab.wcf.article.category',
							$articleEditor->getCategory()->categoryID,
							'article',
							'com.woltlab.wcf.article.notification',
							new ArticleUserNotificationObject($articleEditor->getDecoratedObject())
						);
						
						UserActivityEventHandler::getInstance()->fireEvent('com.woltlab.wcf.article.recentActivityEvent', $articleEditor->articleID, null, $articleEditor->userID, $articleEditor->time);
					}
					else {
						$resetArticleIDs[] = $articleEditor->articleID;
					}
				}
			}
			
			if (!empty($resetArticleIDs)) {
				// delete user notifications
				UserNotificationHandler::getInstance()->removeNotifications('com.woltlab.wcf.article.notification', $resetArticleIDs);
				// delete recent activity events
				UserActivityEventHandler::getInstance()->removeEvents('com.woltlab.wcf.article.recentActivityEvent', $resetArticleIDs);
			}
			
			if (!empty($usersToArticles)) {
				ArticleEditor::updateArticleCounter($usersToArticles);
			}
		}
		
		// update author in recent activities
		if (isset($this->parameters['data']['userID'])) {
			$sql = "UPDATE wcf".WCF_N."_user_activity_event SET userID = ? WHERE objectTypeID = ? AND objectID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($this->objects as $articleEditor) {
				if ($articleEditor->userID != $this->parameters['data']['userID']) {
					$statement->execute([
						$this->parameters['data']['userID'],
						UserActivityEventHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.article.recentActivityEvent'),
						$articleEditor->articleID,
					]);
				}
			}
		}
	}
	
	/**
	 * Validates parameters to delete articles.
	 *
	 * @throws	PermissionDeniedException
	 * @throws	UserInputException
	 */
	public function validateDelete() {
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if (!$article->canDelete()) {
				throw new PermissionDeniedException();
			}
			
			if (!$article->isDeleted) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$usersToArticles = $articleIDs = $articleContentIDs = [];
		foreach ($this->getObjects() as $article) {
			$articleIDs[] = $article->articleID;
			foreach ($article->getArticleContents() as $articleContent) {
				$articleContentIDs[] = $articleContent->articleContentID;
			}
			
			if ($article->publicationStatus == Article::PUBLISHED) {
				if (!isset($usersToArticles[$article->userID])) {
					$usersToArticles[$article->userID] = 0;
				}
				$usersToArticles[$article->userID]--;
			}
		}
		
		// delete articles
		parent::delete();
		
		if (!empty($articleIDs)) {
			// delete like data
			ReactionHandler::getInstance()->removeReactions('com.woltlab.wcf.likeableArticle', $articleIDs);
			// delete comments
			CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.articleComment', $articleContentIDs);
			// delete tag to object entries
			TagEngine::getInstance()->deleteObjects('com.woltlab.wcf.article', $articleContentIDs);
			// delete entry from search index
			SearchIndexManager::getInstance()->delete('com.woltlab.wcf.article', $articleContentIDs);
			// delete user notifications
			UserNotificationHandler::getInstance()->removeNotifications('com.woltlab.wcf.article.notification', $articleIDs);
			// delete recent activity events
			UserActivityEventHandler::getInstance()->removeEvents('com.woltlab.wcf.article.recentActivityEvent', $articleIDs);
			// delete embedded object references
			MessageEmbeddedObjectManager::getInstance()->removeObjects('com.woltlab.wcf.article.content', $articleContentIDs);
			// update wcf1_user.articles
			ArticleEditor::updateArticleCounter($usersToArticles);
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
	 * @throws	PermissionDeniedException
	 * @throws	UserInputException
	 */
	public function validateTrash() {
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if (!$article->canDelete()) {
				throw new PermissionDeniedException();
			}
			
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
			UserStorageHandler::getInstance()->resetAll('unreadWatchedArticles');
			UserStorageHandler::getInstance()->resetAll('unreadArticlesByCategory');
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
			UserStorageHandler::getInstance()->resetAll('unreadWatchedArticles');
			UserStorageHandler::getInstance()->resetAll('unreadArticlesByCategory');
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
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadWatchedArticles');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadArticlesByCategory');
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
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadWatchedArticles');
			UserStorageHandler::getInstance()->reset([WCF::getUser()->userID], 'unreadArticlesByCategory');
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
	 * @throws	PermissionDeniedException
	 * @throws	UserInputException
	 */
	public function validatePublish() {
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if (!$article->canPublish()) {
				throw new PermissionDeniedException();	
			}
			
			if ($article->publicationStatus == Article::PUBLISHED) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Publishes articles.
	 */
	public function publish() {
		$usersToArticles = [];
		foreach ($this->getObjects() as $articleEditor) {
			$articleEditor->update([
				'time' => TIME_NOW,
				'publicationStatus' => Article::PUBLISHED,
				'publicationDate' => 0
			]);
			
			if (!isset($usersToArticles[$articleEditor->userID])) {
				$usersToArticles[$articleEditor->userID] = 0;
			}
			
			$usersToArticles[$articleEditor->userID]++;
			
			UserObjectWatchHandler::getInstance()->updateObject(
				'com.woltlab.wcf.article.category',
				$articleEditor->getCategory()->categoryID,
				'article',
				'com.woltlab.wcf.article.notification',
				new ArticleUserNotificationObject($articleEditor->getDecoratedObject())
			);
			
			UserActivityEventHandler::getInstance()->fireEvent('com.woltlab.wcf.article.recentActivityEvent', $articleEditor->articleID, null, $articleEditor->userID, TIME_NOW);
		}
		
		ArticleEditor::updateArticleCounter($usersToArticles);
		
		// reset storage
		if (ARTICLE_ENABLE_VISIT_TRACKING) {
			UserStorageHandler::getInstance()->resetAll('unreadArticles');
			UserStorageHandler::getInstance()->resetAll('unreadWatchedArticles');
			UserStorageHandler::getInstance()->resetAll('unreadArticlesByCategory');
		}
		
		$this->unmarkItems();
	}
	
	/**
	 * Validates the `unpublish` action.
	 * 
	 * @throws	PermissionDeniedException
	 * @throws	UserInputException
	 */
	public function validateUnpublish() {
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
		
		foreach ($this->getObjects() as $article) {
			if (!$article->canPublish()) {
				throw new PermissionDeniedException();
			}
			
			if ($article->publicationStatus != Article::PUBLISHED) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Unpublishes articles.
	 */
	public function unpublish() {
		$usersToArticles = $articleIDs = [];
		foreach ($this->getObjects() as $articleEditor) {
			$articleEditor->update(['publicationStatus' => Article::UNPUBLISHED]);
			
			if (!isset($usersToArticles[$articleEditor->userID])) {
				$usersToArticles[$articleEditor->userID] = 0;
			}
			
			$usersToArticles[$articleEditor->userID]--;
			
			$articleIDs[] = $articleEditor->articleID;
		}
		
		// delete user notifications
		UserNotificationHandler::getInstance()->removeNotifications('com.woltlab.wcf.article.notification', $articleIDs);
		
		// delete recent activity events
		UserActivityEventHandler::getInstance()->removeEvents('com.woltlab.wcf.article.recentActivityEvent', $articleIDs);
		
		ArticleEditor::updateArticleCounter($usersToArticles);
		
		$this->unmarkItems();
	}
	
	/**
	 * Validates parameters to search for an article by its localized title.
	 */
	public function validateSearch() {
		$this->readString('searchString');
	}
	
	/**
	 * Searches for an article by its localized title.
	 * 
	 * @return      array   list of matching articles
	 */
	public function search() {
		$sql = "SELECT          articleID
			FROM            wcf".WCF_N."_article_content
			WHERE           title LIKE ?
					AND (
						languageID = ?
						OR languageID IS NULL
					)
			ORDER BY        title";
		$statement = WCF::getDB()->prepareStatement($sql, 5);
		$statement->execute([
			'%' . $this->parameters['searchString'] . '%',
			WCF::getLanguage()->languageID,
		]);
		
		$articleIDs = [];
		while ($articleID = $statement->fetchColumn()) {
			$articleIDs[] = $articleID;
		}
		
		$articleList = new ArticleList();
		$articleList->setObjectIDs($articleIDs);
		$articleList->readObjects();
		
		$articles = [];
		foreach ($articleList as $article) {
			$articles[] = [
				'displayLink' => $article->getLink(),
				'name' => $article->getTitle(),
				'articleID' => $article->articleID,	
			];
		}
		
		return $articles;
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
