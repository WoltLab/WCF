<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;

/**
 * Template function plugin which generates simple sliding pagers.
 * 
 * Usage:
 * 	{smallpages pages=10 link='page-%d.html'}
 * 	
 * 	assign to variable 'output'; do not print: 
 * 	{smallpages pages=10 link='page-%d.html' assign='output'}
 * 	
 * 	assign to variable 'output' and do print also:
 * 	{smallpages pages=10 link='page-%d.html' assign='output' print=true}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class SmallpagesFunctionTemplatePlugin extends PagesFunctionTemplatePlugin {
	/**
	 * @see	\wcf\system\template\plugin\PagesFunctionTemplatePlugin::SHOW_LINKS
	 */
	const SHOW_LINKS = 7;
	
	/**
	 * @see	\wcf\system\template\plugin\PagesFunctionTemplatePlugin::makePreviousLink()
	 */
	protected function makePreviousLink($link, $pageNo) {
		return '';
	}
	
	/**
	 * @see	\wcf\system\template\plugin\PagesFunctionTemplatePlugin::makeNextLink()
	 */
	protected function makeNextLink($link, $pageNo, $pages) {
		return '';
	}
	
	/**
	 * @see	\wcf\system\template\IFunctionTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$tagArgs['page'] = 0;
		
		return parent::execute($tagArgs, $tplObj);
	}
}
