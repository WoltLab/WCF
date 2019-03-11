<?php
namespace wcf\system\article\discussion;
use wcf\data\article\Article;
use wcf\data\article\content\ArticleContent;

/**
 * Discussion provider for articles.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Article\Discussion
 * @since       5.2
 */
interface IArticleDiscussionProvider {
	/**
	 * Returns the number of discussion items.
	 * 
	 * @return      int
	 */
	public function getDiscussionCount();
	
	/**
	 * Returns the simple phrase "X <discussions>" that is used for both the statistics
	 * and the meta data in the article's headline.
	 * 
	 * @return      string
	 */
	public function getDiscussionCountPhrase();
	
	/**
	 * Renders the input and display section of the associated discussion.
	 * 
	 * @return      string
	 */
	public function renderDiscussions();
	
	/**
	 * Sets the content object required for the separate discussions per article language.
	 * 
	 * @param       ArticleContent          $articleContent
	 */
	public function setArticleContent(ArticleContent $articleContent);
	
	/**
	 * Returning true will assign this provider to the article, otherwise the next
	 * possible provider is being evaluated.
	 *
	 * @param       Article         $article
	 * @return      bool
	 */
	public static function isResponsible(Article $article);
}
