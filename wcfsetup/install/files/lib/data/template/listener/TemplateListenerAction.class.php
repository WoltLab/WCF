<?php
namespace wcf\data\template\listener;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes template listener-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template\Listener
 * 
 * @method	TemplateListener		create()
 * @method	TemplateListenerEditor[]	getObjects()
 * @method	TemplateListenerEditor		getSingleObject()
 */
class TemplateListenerAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = TemplateListenerEditor::class;
}
