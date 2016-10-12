<?php
namespace wcf\data\article;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\object\type\AbstractObjectTypeProvider;
use wcf\system\like\IViewableLikeProvider;
use wcf\system\WCF;

/**
 * Like Object type provider for cms articles.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 *
 * @method	LikeableArticle		getObjectByID($objectID)
 * @method	LikeableArticle[]		getObjectsByIDs(array $objectIDs)
 */
class LikeableArticleProvider extends AbstractObjectTypeProvider implements ILikeObjectTypeProvider, IViewableLikeProvider {
	/**
	 * @inheritDoc
	 */
	public $className = Article::class;
	
	/**
	 * @inheritDoc
	 */
	public $listClassName = ArticleList::class;
	
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = LikeableArticle::class;
	
	/**
	 * @inheritDoc
	 */
	public function checkPermissions(ILikeObject $object) {
		/** @var LikeableArticle $object */
		return $object->articleID && $object->canRead();
	}
	
	/**
	 * @inheritDoc
	 */
	public function prepare(array $likes) {
		$articleIDs = [];
		foreach ($likes as $like) {
			$articleIDs[] = $like->objectID;
		}
		
		// fetch articles
		$articleList = new ViewableArticleList();
		$articleList->setObjectIDs($articleIDs);
		$articleList->readObjects();
		$articles = $articleList->getObjects();
		
		// set message
		foreach ($likes as $like) {
			if (isset($articles[$like->objectID])) {
				$article = $articles[$like->objectID];
				
				// check permissions
				if (!$article->canRead()) {
					continue;
				}
				$like->setIsAccessible();
				
				// short output
				$text = WCF::getLanguage()->getDynamicVariable('wcf.like.title.com.woltlab.wcf.likeableArticle', [
					'article' => $article,
					'like' => $like
				]);
				$like->setTitle($text);
				
				// output
				$like->setDescription($article->getTeaser());
			}
		}
	}
}
