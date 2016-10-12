<?php
namespace wcf\data\page\content;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes page content related actions.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Page\Content
 * @since	3.0
 * 
 * @method	PageContentEditor[]	getObjects()
 * @method	PageContentEditor	getSingleObject()
 */
class PageContentAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PageContentEditor::class;
}
