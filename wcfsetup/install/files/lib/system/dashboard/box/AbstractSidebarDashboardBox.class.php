<?php
namespace wcf\system\dashboard\box;
use wcf\system\WCF;

/**
 * Default implementation for dashboard boxes displayed within the sidebar container.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.dashboard.box
 * @category	Community Framework
 */
abstract class AbstractSidebarDashboardBox extends AbstractContentDashboardBox {
	/**
	 * @see	\wcf\system\dashboard\box\AbstractDashboardBoxContent::$templateName
	 */
	public $templateName = 'dashboardBoxSidebar';
	
	/**
	 * title link
	 * @var	string
	 */
	public $titleLink = '';
	
	/**
	 * @see	\wcf\system\dashboard\box\IDashboardBox::getTemplate()
	 */
	public function getTemplate() {
		$template = $this->render();
		if (empty($template)) {
			return '';
		}
		
		WCF::getTPL()->assign(array(
			'box' => $this->box,
			'template' => $template,
			'titleLink' => $this->titleLink
		));
		
		return WCF::getTPL()->fetch($this->templateName);
	}
}
