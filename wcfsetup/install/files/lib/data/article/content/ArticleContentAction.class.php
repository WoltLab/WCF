<?php
namespace wcf\data\article\content;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\comment\CommentHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\tagging\TagEngine;

/**
 * Executes article content related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
 * 
 * @method	ArticleContentEditor[]	getObjects()
 * @method	ArticleContentEditor	getSingleObject()
 */
class ArticleContentAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ArticleContentEditor::class;
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$articleContentIDs = [];
		foreach ($this->getObjects() as $contentEditor) {
			$articleContentIDs[] = $contentEditor->getDecoratedObject()->articleContentID;
		}
		
		// delete comments
		CommentHandler::getInstance()->deleteObjects('com.woltlab.wcf.articleComment', $articleContentIDs);
		// delete tag to object entries
		TagEngine::getInstance()->deleteObjects('com.woltlab.wcf.article', $articleContentIDs);
		// delete entry from search index
		SearchIndexManager::getInstance()->delete('com.woltlab.wcf.article', $articleContentIDs);
	}
}
