<?php
namespace wcf\system\message\embedded\object;
use wcf\data\article\Article;
use wcf\data\article\ArticleList;
use wcf\system\html\input\HtmlInputProcessor;

/**
 * Parses embedded articles and outputs their link or title.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Message\Embedded\Object
 */
class ArticleMessageEmbeddedObjectHandler extends AbstractSimpleMessageEmbeddedObjectHandler {
	/**
	 * @inheritDoc
	 */
	public function parse(HtmlInputProcessor $htmlInputProcessor, array $embeddedData) {
		$articleIDs = [];
		if (!empty($embeddedData['wsa'])) {
			for ($i = 0, $length = count($embeddedData['wsa']); $i < $length; $i++) {
				$articleIDs[] = intval($embeddedData['wsa'][$i][0]);
			}
		}
		
		return array_unique($articleIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public function loadObjects(array $objectIDs) {
		$articleList = new ArticleList();
		$articleList->setObjectIDs($objectIDs);
		$articleList->readObjects();
		
		return $articleList->getObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateValues($objectType, $objectID, array $values) {
		$articleList = new ArticleList();
		$articleList->setObjectIDs($values);
		$articleList->readObjects();
		$articles = $articleList->getObjects();
		
		return array_filter($values, function($value) use ($articles) {
			return isset($articles[$value]);
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceSimple($objectType, $objectID, $value, array $attributes) {
		/** @var Article $article */
		$article = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.article', $value);
		if ($article === null) {
			return null;
		}
		
		$return = (!empty($attributes['return'])) ? $attributes['return'] : 'link';
		switch ($return) {
			case 'title':
				return $article->getTitle();
				break;
			
			case 'link':
			default:
				return $article->getLink();
				break;
		}
	}
}
