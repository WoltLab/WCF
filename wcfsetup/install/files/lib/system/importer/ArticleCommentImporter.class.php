<?php
namespace wcf\system\importer;
use wcf\data\article\Article;
use wcf\data\object\type\ObjectTypeCache;

/**
 * Imports article comments.
 *
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class ArticleCommentImporter extends AbstractCommentImporter {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.woltlab.wcf.article.comment';
	
	/**
	 * Creates a new ArticleCommentImporter object.
	 */
	public function __construct() {
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.comment.commentableContent', 'com.woltlab.wcf.articleComment');
		$this->objectTypeID = $objectType->objectTypeID;
	}
	
	/**
	 * @inheritDoc
	 */
	public function import($oldID, array $data, array $additionalData = []) {
		$articleID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.article', $data['objectID'] ?? $additionalData['articleID']);
		if (!$articleID) return 0;
		$article = new Article($articleID);
		$contents = $article->getArticleContents();
		$data['objectID'] = reset($contents)->articleContentID;
		
		return parent::import($oldID, $data);
	}
}
