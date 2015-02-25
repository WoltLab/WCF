<?php
namespace wcf\acp\action;
use wcf\action\AbstractAction;
use wcf\data\option\Option;
use wcf\util\StringUtil;

/**
 * Exports the options to an XML.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category	Community Framework
 */
class OptionExportAction extends AbstractAction {
	/**
	 * @see	\wcf\action\AbstractAction::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canEditOption');
	
	/**
	 * @see	\wcf\action\IAction::execute();
	 */
	public function execute() {
		parent::execute();
		
		// header
		@header('Content-type: text/xml');
		
		// file name
		@header('Content-disposition: attachment; filename="options.xml"');
			
		// no cache headers
		@header('Pragma: no-cache');
		@header('Expires: 0');
		
		// content
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<options>\n";
		
		$options = Option::getOptions();
		foreach ($options as $option) {
			if ($option->hidden) continue; // ignore hidden options
			
			echo "\t<option>\n";
			echo "\t\t<name><![CDATA[".StringUtil::escapeCDATA($option->optionName)."]]></name>\n";
			echo "\t\t<value><![CDATA[".StringUtil::escapeCDATA($option->optionValue)."]]></value>\n";
			echo "\t</option>\n";
		}
		
		echo '</options>';
		$this->executed();
		exit;
	}
}
