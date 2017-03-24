<?php
namespace wcf\data\article;
use wcf\data\article\content\ArticleContent;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\IVersionTrackerObject;

/**
 * Represents an article with version tracking.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 *
 * @method	Article	        getDecoratedObject()
 * @mixin	Article
 */
class ArticleVersionTracker extends DatabaseObjectDecorator implements IVersionTrackerObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Article::class;
	
	/**
	 * list of article content objects
	 * @var ArticleContent[]
	 */
	protected $content = [];
	
	/**
	 * @inheritDoc
	 */
	public function getObjectID() {
		return $this->getDecoratedObject()->articleID;
	}
	
	/**
	 * Adds an article content object as child.
	 * 
	 * @param       ArticleContent  $content        article content object
	 */
	public function addContent(ArticleContent $content) {
		$this->content[] = $content;
	}
	
	/**
	 * Sets the list of article content objects.
	 * 
	 * @param       ArticleContent[]        $content        article content objects
	 */
	public function setContent(array $content) {
		$this->content = $content;
	}
	
	/**
	 * Returns the list of stored article content objects.
	 * 
	 * @return      ArticleContent[]        stored article content objects
	 */
	public function getContent() {
		return $this->content;
	}
}