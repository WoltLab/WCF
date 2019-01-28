<?php
namespace wcf\data\article;
use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit cms articles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 * 
 * @method static	Article		create(array $parameters = [])
 * @method		Article		getDecoratedObject()
 * @mixin		Article
 */
class ArticleEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Article::class;
	
	/**
	 * Updates the article counter of the given user ids.
	 * 
	 * @param       int[]   $users  user id => article counter increase/decrease
	 * @since       5.2
	 */
	public static function updateArticleCounter(array $users) {
		$sql = "UPDATE	wcf".WCF_N."_user
			SET	articles = articles + ?
			WHERE	userID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		foreach ($users as $userID => $articles) {
			$statement->execute([$articles, $userID]);
		}
	}
}
