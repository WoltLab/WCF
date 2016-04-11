<?php
namespace wcf\system\dashboard\box;
use wcf\data\dashboard\box\DashboardBox;
use wcf\page\IPage;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Default implementation for dashboard boxes displayed within content container.
 * 
 * @author	Alexander Ebert, Joshua Rüsweg
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
abstract class AbstractContentDashboardBox implements IDashboardBox {
	/**
	 * needed modules to view this dashboard box
	 * @var	array<string>
	 */
	public $neededModules = array();
	
	/**
	 * needed permissions to view this dashboard box
	 * @var	array<string>
	 */
	public $neededPermissions = array();
	
	/**
	 * dashboard box object
	 * @var	\wcf\data\dashboard\box\DashboardBox
	 */
	public $box = null;
	
	/**
	 * IPage object
	 * @var	\wcf\page\IPage
	 */
	public $page = null;
	
	/**
	 * template name
	 * @var	string
	 */
	public $templateName = 'dashboardBoxContent';
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::init()
	 */
	public function init(DashboardBox $box, IPage $page) {
		$this->box = $box;
		$this->page = $page;
		
		// fire event
		EventHandler::getInstance()->fireAction($this, 'init');
	}
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::getTemplate()
	 */
	public function getTemplate() {
		// check modules
		foreach ($this->neededModules as $module) {
			if (!defined($module) || !constant($module)) {
				return ''; 
			}
		}
		
		// check permission, it is sufficient to have at least one permission
		if (!empty($this->neededPermissions)) {
			$hasPermissions = false;
			foreach ($this->neededPermissions as $permission) {
				if (WCF::getSession()->getPermission($permission)) {
					$hasPermissions = true;
					break;
				}
			}
			
			if (!$hasPermissions) {
				return ''; 
			}
		}
		
		$template = $this->render();
		if (empty($template)) {
			return '';
		}
		
		WCF::getTPL()->assign(array(
			'box' => $this->box,
			'template' => $template
		));
		
		return WCF::getTPL()->fetch($this->templateName);
	}
	
	/**
	 * Calls the 'fetched' event after the successful call of the init method.
	 * This functions won't called automatically. You must do this manually, if you inherit AbstractContentDashboardBox.
	 */
	protected function fetched() {
		// fire event
		EventHandler::getInstance()->fireAction($this, 'fetched');
	}
	
	/**
	 * Renders box view.
	 * 
	 * @return	string
	 */
	abstract protected function render();
}
