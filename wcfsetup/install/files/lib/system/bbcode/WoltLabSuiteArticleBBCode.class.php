<?php
namespace wcf\system\bbcode;
use wcf\data\article\Article;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\util\StringUtil;

/**
 * Parses the [wsa] bbcode tag.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Bbcode
 * @since       5.2
 */
class WoltLabSuiteArticleBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		$articleID = (!empty($openingTag['attributes'][0])) ? intval($openingTag['attributes'][0]) : 0;
		if (!$articleID) {
			return '';
		}
		
		$title = (!empty($openingTag['attributes'][1])) ? StringUtil::trim($openingTag['attributes'][1]) : '';
		
		/** @var Article $article */
		$article = MessageEmbeddedObjectManager::getInstance()->getObject('com.woltlab.wcf.article', $articleID);
		if ($article !== null) {
			return StringUtil::getAnchorTag($article->getLink(), $title ?: $article->getTitle());
		}
		
		return '';
	}
}
