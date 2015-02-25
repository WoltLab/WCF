<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows phpinfo() output.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class PHPInfoPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$templateName
	 */
	public $templateName = 'phpInfo';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.package.canInstallPackage', 'admin.system.package.canUpdatePackage');
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// get phpinfo() output
		ob_start();
		phpinfo();
		$info = ob_get_contents();
		ob_end_clean();
		
		// parse output
		$info = preg_replace('%^.*<body>(.*)</body>.*$%s', '$1', $info);
		
		// style fixes
		// remove first table
		$info = preg_replace('%<table.*?</table>(<br />)?%s', '', $info, 1);
		// float logos
		$info = preg_replace('%<img([^>]*)>%s', '<img style="float:right" \\1>', $info, 1);
		
		// fix tables
		$info = preg_replace('%<h2>(.*?)</h2>\s*<table( border="0" cellpadding="3" width="600")?>%', '<div class="tabularBox tabularBoxTitle marginTop"><header><h2>\\1</h2></header><table class="table" style="table-layout:fixed;">', $info);
		$info = preg_replace('%<table( border="0" cellpadding="3" width="600")?>%', '<div class="tabularBox marginTop"><table class="table" style="table-layout:fixed;">', $info);
		$info = preg_replace('%<tr><td class="e">(\w+ )<\/td><\/tr>%', '<tr><td class="e">\\1</td><td></td></tr>', $info);
		$info = str_replace('</table>', '</table></div>', $info);
		
		// fix display of disable_functions & disable_classes
		$info = preg_replace_callback('%<td class="e">disable_(?P<t>functions|classes)</td><td class="v">(?P<l>.*?)</td><td class="v">(?P<m>.*?)</td>%s', function ($match) {
			$ret = '<td class="e">disable_' . $match['t'] . '</td>';
			$ret .= '<td class="v">' . str_replace(' ', ', ', rtrim(wordwrap(str_replace(',', ' ', $match['l'])))) . '</td>';
			$ret .= '<td class="v">' . str_replace(' ', ', ', rtrim(wordwrap(str_replace(',', ' ', $match['m'])))) . '</td>';
			
			return $ret;
		}, $info);
		
		WCF::getTPL()->assign(array(
			'phpInfo' => $info
		));
	}
}
