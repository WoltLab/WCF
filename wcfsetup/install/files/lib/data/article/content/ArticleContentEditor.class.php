<?php
namespace wcf\data\article\content;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit article content.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article.content
 * @category	Community Framework
 * @since	2.2
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
