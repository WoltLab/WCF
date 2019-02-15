<?php
namespace wcf\system\article\discussion;
use wcf\data\article\Article;
use wcf\data\article\content\ArticleContent;

/**
 * Default implementation for discussion provider for articles. Any actual implementation
 * should derive from this class for forwards-compatibility.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Article\Discussion
 * @since       5.2
 */
abstract class AbstractArticleDiscussionProvider implements IArticleDiscussionProvider {
	/**
	 * @var Article
	 */
	protected $article;
	
	/**
	 * @var ArticleContent 
	 */
	protected $articleContent;
	
	/**
	 * AbstractArticleDiscussionProvider constructor.
	 *
	 * @param       Article         $article
	 */
	public function __construct(Article $article) {
		$this->article = $article;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setArticleContent(ArticleContent $articleContent) {
		$this->articleContent = $articleContent;
	}
}
