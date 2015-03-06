<?php
namespace wcf\data\template;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\language\LanguageFactory;

/**
 * Executes template-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.template
 * @category	Community Framework
 */
class TemplateAction extends AbstractDatabaseObjectAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$className
	 */
	protected $className = 'wcf\data\template\TemplateEditor';
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsCreate
	 */
	protected $permissionsCreate = array('admin.template.canManageTemplate');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsDelete
	 */
	protected $permissionsDelete = array('admin.template.canManageTemplate');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$permissionsUpdate
	 */
	protected $permissionsUpdate = array('admin.template.canManageTemplate');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$requireACP
	 */
	protected $requireACP = array('create', 'delete', 'update');
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::create()
	 */
	public function create() {
		$template = parent::create();
		
		if (isset($this->parameters['source'])) {
			$editor = new TemplateEditor($template);
			$editor->setSource($this->parameters['source']);
		}
		
		return $template;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::delete()
	 */
	public function delete() {
		$count = parent::delete();
		
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		return $count;
	}
	
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::update()
	 */
	public function update() {
		parent::update();
		
		foreach ($this->objects as $template) {
			// rename file
			$templateName = (isset($this->parameters['data']['templateName']) ? $this->parameters['data']['templateName'] : $template->templateName);
			$templateGroupID = (isset($this->parameters['data']['templateGroupID']) ? $this->parameters['data']['templateGroupID'] : $template->templateGroupID);
			if ($templateName != $template->templateName || $templateGroupID != $template->templateGroupID) {
				$template->rename($templateName, $templateGroupID);
			}
			
			// update source
			if (isset($this->parameters['source'])) {
				$template->setSource($this->parameters['source']);
			}
		}
	}
}
