<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\request\RouteHandler;
use wcf\system\style\StyleHandler;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;
use wcf\util\StringUtil;


/**
 * The 'pages' template function is used to generate sliding pagers.
 * 
 * Usage:
 * {pages pages=10 link='page-%d.html'}
 * {pages page=8 pages=10 link='page-%d.html'}
 * 
 * assign to variable 'output'; do not print: 
 * {pages page=8 pages=10 link='page-%d.html' assign='output'}
 * 
 * assign to variable 'output' and do print also:
 * {pages page=8 pages=10 link='page-%d.html' assign='output' print=true}
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class PagesFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	const SHOW_LINKS = 11;
	const SHOW_SUB_LINKS = 20;
	
	/**
	 * Inserts the page number into the link.
	 * 
	 * @param 	string		$link
	 * @param 	integer		$pageNo
	 * @return	string		final link
	 */
	protected static function insertPageNumber($link, $pageNo) {
		$startPos = StringUtil::indexOf($link, '%d');
		if ($startPos !== null) $link = StringUtil::substring($link, 0, $startPos) . $pageNo . StringUtil::substring($link, $startPos + 2);
		return $link;
	}
	
	/**
	 * Generates html code of a link.
	 * 
	 * @param 	string		$link
	 * @param 	integer		$pageNo
	 * @param 	integer		$activePage
	 * @param	boolean		$break
	 * @return	string
	 */
	protected function makeLink($link, $pageNo, $activePage, $break = false) {
		// first page
		if ($activePage != $pageNo) {
			return '<li'.($break ? ' class="break"' : '').'><a href="'.$this->insertPageNumber($link, $pageNo).'">'.StringUtil::formatInteger($pageNo).'</a></li>'."\n";
		}
		else {
			return '<li class="'.($break ? 'break ' : '').'active"><span>'.StringUtil::formatInteger($pageNo).'</span></li>'."\n";
		}
	}
	
	/**
	 * @see wcf\system\template\IFunctionTemplatePlugin::execute()
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// needed params: controller, link, page, pages
		if (!isset($tagArgs['link'])) throw new SystemException("missing 'link' argument in pages tag");
		if (!isset($tagArgs['controller'])) throw new SystemException("missing 'controller' argument in pages tag");
		if (!isset($tagArgs['pages'])) {
			if (($tagArgs['pages'] = $tplObj->get('pages')) === null) {
				throw new SystemException("missing 'pages' argument in pages tag");
			}
		}
		
		$html = '';
		
		if ($tagArgs['pages'] > 1) {
			// create and encode route link
			$parameters = array();
			if (isset($tagArgs['id'])) $parameters['id'] = $tagArgs['id'];
			if (isset($tagArgs['title'])) $parameters['title'] = $tagArgs['title'];
			if (isset($tagArgs['object'])) $parameters['object'] = $tagArgs['object'];
			if (isset($tagArgs['application'])) $parameters['application'] = $tagArgs['application'];
			
			$link = StringUtil::encodeHTML(LinkHandler::getInstance()->getLink($tagArgs['controller'], $parameters, $tagArgs['link']));
					
			if (!isset($tagArgs['page'])) {
				if (($tagArgs['page'] = $tplObj->get('pageNo')) === null) {
					$tagArgs['page'] = 0;
				}
			}
			
			// open div and ul
			$html .= "<div class=\"pageNavigation\" data-link=\"".$link."\">\n<ul>\n";
			
			// previous page
			$previousTitle = WCF::getLanguage()->getDynamicVariable('wcf.global.page.previous');
			if ($tagArgs['page'] > 1) {
				$html .= '<li class="skip"><a href="'.$this->insertPageNumber($link, $tagArgs['page'] - 1).'" title="'.$previousTitle.'" class="balloonTooltip"><img src="'.self::getIconPath('previous1').'" alt="" /></a></li>'."\n";
			}
			else {
				$html .= '<li class="skip disabled"><img src="'.self::getIconPath('previous1D').'" alt="" /></li>'."\n";
			}
			
			// first page
			$html .= $this->makeLink($link, 1, $tagArgs['page']);
			
			// calculate page links
			$maxLinks = self::SHOW_LINKS - 4;
			$linksBeforePage = $tagArgs['page'] - 2;
			if ($linksBeforePage < 0) $linksBeforePage = 0; 
			$linksAfterPage = $tagArgs['pages'] - ($tagArgs['page'] + 1);
			if ($linksAfterPage < 0) $linksAfterPage = 0; 
			if ($tagArgs['page'] > 1 && $tagArgs['page'] < $tagArgs['pages']) {
				$maxLinks--;
			}
			
			$half = $maxLinks / 2;
			$left = $right = $tagArgs['page'];
			if ($left < 1) $left = 1;
			if ($right < 1) $right = 1;
			if ($right > $tagArgs['pages'] - 1) $right = $tagArgs['pages'] - 1;
			
			if ($linksBeforePage >= $half) {
				$left -= $half;
			}
			else {
				$left -= $linksBeforePage;
				$right += $half - $linksBeforePage;
			}
			
			if ($linksAfterPage >= $half) {
				$right += $half;
			}
			else {
				$right += $linksAfterPage;
				$left -= $half - $linksAfterPage;
			}
			
			$right = intval(ceil($right));
			$left = intval(ceil($left));
			if ($left < 1) $left = 1;
			if ($right > $tagArgs['pages']) $right = $tagArgs['pages'];
			
			// left ... links
			if ($left > 1) {
				if ($left - 1 < 2) {
					$html .= $this->makeLink($link, 2, $tagArgs['page']);
				}
				else {
					$html .= '<li class="children"><a onclick="multiPagesLinks.startPageNumberInput(this)">&hellip;<img src="'.self::getIconPath('arrowDown').'" alt="" /></a><input type="text" name="pageNo" class="tiny" /><div><ul>'."\n";
					
					$k = 0;
					$step = intval(ceil(($left - 2) / self::SHOW_SUB_LINKS));
					for ($i = 2; $i <= $left; $i += $step) {
						$html .= $this->makeLink($link, $i, $tagArgs['page'], $k != 0 && $k % 4 == 0);
						$k++;
					}
					
					$html .= "</ul></div></li>\n";
				}
			}
			
			// visible links
			for ($i = $left + 1; $i < $right; $i++) {
				$html .= $this->makeLink($link, $i, $tagArgs['page']);
			}
			
			// right ... links
			if ($right < $tagArgs['pages']) {
				if ($tagArgs['pages'] - $right < 2) {
					$html .= $this->makeLink($link, $tagArgs['pages'] - 1, $tagArgs['page']);
				}
				else {
					$html .= '<li class="children"><a onclick="multiPagesLinks.startPageNumberInput(this)">&hellip;<img src="'.self::getIconPath('dropdown1').'" alt="" /></a><input type="text" name="page" class="tiny" /><div><ul>'."\n";
					
					$k = 0;
					$step = intval(ceil(($tagArgs['pages'] - $right) / self::SHOW_SUB_LINKS));
					for ($i = $right; $i < $tagArgs['pages']; $i += $step) {
						$html .= $this->makeLink($link, $i, $tagArgs['page'], $k != 0 && $k % 4 == 0);
						$k++;
					}
					
					$html .= "</ul></div></li>\n";
				}
			}
			
			// last page
			$html .= $this->makeLink($link, $tagArgs['pages'], $tagArgs['page']);
			
			// next page
			$nextTitle = WCF::getLanguage()->getDynamicVariable('wcf.global.page.next');
			if ($tagArgs['page'] && $tagArgs['page'] < $tagArgs['pages']) {
				$html .= '<li class="skip"><a href="'.$this->insertPageNumber($link, $tagArgs['page'] + 1).'" title="'.$nextTitle.'" class="balloonTooltip"><img src="'.self::getIconPath('next1').'" alt="" /></a></li>'."\n";
			}
			else {
				$html .= '<li class="skip disabled"><img src="'.self::getIconPath('next1D').'" alt="" /></li>'."\n";
			}
			
			// close div and ul
			$html .= "</ul></div>\n";
		}
		
		// assign html output to template var
		if (isset($tagArgs['assign'])) {
			$tplObj->assign($tagArgs['assign'], $html);
			if (!isset($tagArgs['print']) || !$tagArgs['print']) return '';
		}
		
		return $html;
	}
	
	private static function getIconPath($iconName) {
		if (class_exists('wcf\system\WCFACP', false)) {
			return RELATIVE_WCF_DIR.'icon/'.$iconName.'.svg';
		}
		else {
			return StyleHandler::getInstance()->getStyle()->getIconPath($iconName, 'S');
		}
	}
}
