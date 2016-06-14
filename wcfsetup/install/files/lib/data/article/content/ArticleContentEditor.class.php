<?php
namespace wcf\data\article\content;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit article content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
 * 
 * @method	ArticleContent	getDecoratedObject()
 * @mixin	ArticleContent
 */
class ArticleContentEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ArticleContent::class;
}
