<?php
namespace wcf\data\article;
use wcf\data\article\content\ArticleContent;
use wcf\data\article\content\ArticleContentEditor;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\comment\CommentHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\like\LikeHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;

/**
 * Executes article related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	ArticleEditor[]	getObjects()
 * @method	ArticleEditor	getSingleObject()
 */
class ArticleAction extends AbstractDatabaseObjectAction {
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
	protected $requireACP = ['create', 'delete', 'update'];
	
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
				/** @var ArticleContent $articleContent */
				$articleContent = ArticleContentEditor::create([
					'articleID' => $article->articleID,
					'languageID' => ($languageID ?: null),
					'title' => $content['title'],
					'teaser' => $content['teaser'],
					'content' => $content['content'],
					'imageID' => $content['imageID']
				]);
				
				// save tags
				if (!empty($content['tags'])) {
					TagEngine::getInstance()->addObjectTags('com.woltlab.wcf.article', $articleContent->articleContentID, $content['tags'], ($languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()));
				}
				
				// update search index
				SearchIndexManager::getInstance()->add('com.woltlab.wcf.article', $articleContent->articleContentID, $articleContent->content, $articleContent->title, $article->time, $article->userID, $article->username, ($languageID ?: null), $articleContent->teaser);
			}
		}
		
		return $article;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		// update article content
		if (!empty($this->parameters['content'])) {
			foreach ($this->getObjects() as $article) {
				foreach ($this->parameters['content'] as $languageID => $content) {
					$articleContent = ArticleContent::getArticleContent($article->articleID, ($languageID ?: null));
					if ($articleContent !== null) {
						// update
						$editor = new ArticleContentEditor($articleContent);
						$editor->update([
							'title' => $content['title'],
							'teaser' => $content['teaser'],
							'content' => $content['content'],
							'imageID' => $content['imageID']
						
						]);
						
						// delete tags
						if (empty($content['tags'])) {
							TagEngine::getInstance()->deleteObjectTags('com.woltlab.wcf.article', $articleContent->articleContentID, ($languageID ?: null));
						}
					}
					else {
						$articleContent = ArticleContentEditor::create([
							'articleID' => $article->articleID,
							'languageID' => ($languageID ?: null),
							'title' => $content['title'],
							'teaser' => $content['teaser'],
							'content' => $content['content'],
							'imageID' => $content['imageID']
						]);
					}
					
					// save tags
					if (!empty($content['tags'])) {
						TagEngine::getInstance()->addObjectTags('com.woltlab.wcf.article', $articleContent->articleContentID, $content['tags'], ($languageID ?: LanguageFactory::getInstance()->getDefaultLanguageID()));
					}
					
					// update search index
					SearchIndexManager::getInstance()->add('com.woltlab.wcf.article', $articleContent->articleContentID, $articleContent->content, $articleContent->title, $article->time, $article->userID, $article->username, ($languageID ?: null), $articleContent->teaser);
				}
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
			foreach ($article->getArticleContent() as $articleContent) {
				$articleContentIDs[] = $articleContent->articleContentID;
			}
		}
		
		// delete articles
		parent::delete();
		
		if (!empty($articleIDs)) {
			// delete like data
			LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.likeableArticle', $articleIDs);
			// delete comments
			CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.article', $articleContentIDs);
			// delete tag to object entries
			TagEngine::getInstance()->deleteObjects('com.woltlab.wcf.article', $articleContentIDs);
			// delete entry from search index
			SearchIndexManager::getInstance()->delete('com.woltlab.wcf.article', $articleContentIDs);
		}
	}
}
