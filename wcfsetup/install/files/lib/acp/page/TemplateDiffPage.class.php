<?php
namespace wcf\acp\page;
use wcf\data\template\group\TemplateGroupList;
use wcf\data\template\Template;
use wcf\data\template\TemplateList;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;
use wcf\util\Diff;
use wcf\util\StringUtil;

/**
 * Compares two templates.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class TemplateDiffPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template';
	
	/**
	 * template id
	 * @var	integer
	 */
	public $templateID = 0;
	
	/**
	 * template object
	 * @var	\wcf\data\template\Template
	 */
	public $template = null;
	
	/**
	 * template id of the template to compare with
	 * @var	integer
	 */
	public $parentID = 0;
	
	/**
	 * template to compare with
	 * @var	\wcf\data\template\Template
	 */
	public $parent = null;
	
	/**
	 * differences between both templates
	 * @var	\wcf\util\Diff
	 */
	public $diff = null;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->templateID = intval($_REQUEST['id']);
		$this->template = new Template($this->templateID);
		if (!$this->template->templateID) {
			throw new IllegalLinkException();
		}
		if (isset($_REQUEST['parentID'])) $this->parentID = intval($_REQUEST['parentID']);
		$this->parent = new Template($this->parentID);
		if ($this->parent->templateID) {
			if ($this->parent->templateName != $this->template->templateName || $this->parent->application != $this->template->application) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		// read out template groups
		$templateGroupList = new TemplateGroupList();
		$templateGroupList->readObjects();
		
		// build template group hierarchy (template groups that are parents of the template group of the selected template)
		$this->templateGroupHierarchy = array();
		$templateGroup = $templateGroupList->search($this->template->templateGroupID);
		while ($templateGroup !== null) {
			$this->templateGroupHierarchy[$templateGroup->templateGroupID] = array('group' => $templateGroup, 'hasTemplate' => false);
			$templateGroup = $templateGroupList->search($templateGroup->parentTemplateGroupID);
		}
		$this->templateGroupHierarchy[0] = array('group' => array(), 'hasTemplate' => false);
		
		// find matching templates in the hierarchy
		$templateList = new TemplateList();
		$templateList->getConditionBuilder()->add('templateName = ?', array($this->template->templateName));
		$templateList->getConditionBuilder()->add('application = ?', array($this->template->application));
		$templateList->getConditionBuilder()->add('(template.templateGroupID IN(?) OR template.templateGroupID IS NULL)', array(array_keys($this->templateGroupHierarchy)));
		$templateList->readObjects();
		foreach ($templateList as $template) {
			$this->templateGroupHierarchy[($template->templateGroupID ?: 0)]['hasTemplate'] = $template->templateID;
		}
		
		// a valid parent template was given, calculate diff
		if ($this->parent->templateID) {
			$a = explode("\n", StringUtil::unifyNewlines($this->parent->getSource()));
			$b = explode("\n", StringUtil::unifyNewlines($this->template->getSource()));
			$this->diff = new Diff($a, $b);
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'templateID' => $this->templateID,
			'template' => $this->template,
			'parentID' => $this->parentID,
			'parent' => $this->parent,
			'diff' => $this->diff,
			'templateGroupHierarchy' => array_reverse($this->templateGroupHierarchy, true)
		));
	}
}
