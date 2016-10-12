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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class SmallpagesFunctionTemplatePlugin extends PagesFunctionTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	const SHOW_LINKS = 7;
	
	/**
	 * @inheritDoc
	 */
	protected $cssClassName = 'pagination small';
	
	/**
	 * @inheritDoc
	 */
	protected function makePreviousLink($link, $pageNo) {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function makeNextLink($link, $pageNo, $pages) {
		return '';
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$tagArgs['page'] = 0;
		
		return parent::execute($tagArgs, $tplObj);
	}
}
