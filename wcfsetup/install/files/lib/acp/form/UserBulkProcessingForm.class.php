<?php
namespace wcf\acp\form;

/**
 * Shows the user bulk processing form.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.form
 * @category	Community Framework
 */
class UserBulkProcessingForm extends AbstractBulkProcessingForm {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.bulkProcessing';
	
	/**
	 * @see	\wcf\acp\form\AbstractBulkProcessingForm::$objectTypeName
	 */
	public $objectTypeName = 'com.woltlab.wcf.bulkProcessing.user';
}
