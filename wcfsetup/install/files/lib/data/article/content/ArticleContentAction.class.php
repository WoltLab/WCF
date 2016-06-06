<?php
namespace wcf\data\article\content;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes article content related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.article.content
 * @category	Community Framework
 * @since	2.2
 * 
 * @method	ArticleContentEditor[]	getObjects()
 * @method	ArticleContentEditor	getSingleObject()
 */
class ArticleContentAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = ArticleContentEditor::class;
}
