<?php
namespace wcf\data\template;
use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\language\LanguageFactory;

/**
 * Executes template-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Template
 * 
 * @method	TemplateEditor[]	getObjects()
 * @method	TemplateEditor		getSingleObject()
 */
class TemplateAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = TemplateEditor::class;
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsCreate = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsDelete = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $permissionsUpdate = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	protected $requireACP = ['create', 'delete', 'update'];
	
	/**
	 * @inheritDoc
	 * @return	Template
	 */
	public function create() {
		/** @var Template $template */
		$template = parent::create();
		
		if (isset($this->parameters['source'])) {
			$editor = new TemplateEditor($template);
			$editor->setSource($this->parameters['source']);
		}
		
		return $template;
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		$count = parent::delete();
		
		LanguageFactory::getInstance()->deleteLanguageCache();
		
		return $count;
	}
	
	/**
	 * @inheritDoc
	 */
	public function update() {
		parent::update();
		
		foreach ($this->getObjects() as $template) {
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
