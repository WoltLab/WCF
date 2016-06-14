<?php
namespace wcf\acp\page;
use wcf\page\AbstractPage;
use wcf\system\WCF;

/**
 * Shows phpinfo() output.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class PHPInfoPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $templateName = 'phpInfo';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage', 'admin.configuration.package.canUpdatePackage'];
	
	/**
	 * @inheritDoc
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
		$info = preg_replace('%<h2>(.*?)</h2>\s*<table( border="0" cellpadding="3" width="600")?>%', '<section class="section tabularBox"><h2 class="sectionTitle">\\1</h2><table class="table" style="table-layout:fixed;">', $info);
		$info = preg_replace('%<table( border="0" cellpadding="3" width="600")?>%', '<section class="section tabularBox"><table class="table" style="table-layout:fixed;">', $info);
		$info = preg_replace('%<tr><td class="e">(\w+ )<\/td><\/tr>%', '<tr><td class="e">\\1</td><td></td></tr>', $info);
		$info = str_replace('</table>', '</table></section>', $info);
		
		// fix display of disable_functions & disable_classes
		$info = preg_replace_callback('%<td class="e">disable_(?P<t>functions|classes)</td><td class="v">(?P<l>.*?)</td><td class="v">(?P<m>.*?)</td>%s', function ($match) {
			$ret = '<td class="e">disable_' . $match['t'] . '</td>';
			$ret .= '<td class="v">' . str_replace(' ', ', ', rtrim(wordwrap(str_replace(',', ' ', $match['l'])))) . '</td>';
			$ret .= '<td class="v">' . str_replace(' ', ', ', rtrim(wordwrap(str_replace(',', ' ', $match['m'])))) . '</td>';
			
			return $ret;
		}, $info);
		
		WCF::getTPL()->assign([
			'phpInfo' => $info
		]);
	}
}
