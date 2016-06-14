<?php
namespace wcf\data\article\content;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes article content related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Article\Content
 * @since	3.0
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
