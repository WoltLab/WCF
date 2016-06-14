<?php
namespace wcf\data\article;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit cms articles.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article
 * @since	3.0
 * 
 * @method	Article	getDecoratedObject()
 * @mixin	Article
 */
class ArticleEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Article::class;
}
