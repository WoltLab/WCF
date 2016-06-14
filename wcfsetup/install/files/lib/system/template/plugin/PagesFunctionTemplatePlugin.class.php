<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Template function plugin which generates sliding pagers.
 * 
 * Usage:
 * 	{pages pages=10 link='page-%d.html'}
 * 	{pages page=8 pages=10 link='page-%d.html'}
 * 	
 * 	assign to variable 'output'; do not print: 
 * 	{pages page=8 pages=10 link='page-%d.html' assign='output'}
 * 	
 * 	assign to variable 'output' and do print also:
 * 	{pages page=8 pages=10 link='page-%d.html' assign='output' print=true}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class PagesFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	const SHOW_LINKS = 11;
	
	/**
	 * CSS class name for <nav> element
	 * @var	string
	 */
	protected $cssClassName = 'pagination';
	
	/**
	 * Inserts the page number into the link.
	 * 
	 * @param	string		$link
	 * @param	integer		$pageNo
	 * @return	string		final link
	 */
	protected static function insertPageNumber($link, $pageNo) {
		$startPos = mb_strpos($link, '%d');
		if ($startPos !== null) $link = mb_substr($link, 0, $startPos) . $pageNo . mb_substr($link, $startPos + 2);
		return $link;
	}
	
	/**
	 * Generates HTML code for a link.
	 * 
	 * @param	string		$link
	 * @param	integer		$pageNo
	 * @param	integer		$activePage
	 * @param	integer		$pages
	 * @return	string
	 */
	protected function makeLink($link, $pageNo, $activePage, $pages) {
		// first page
		if ($activePage != $pageNo) {
			return '<li><a href="'.$this->insertPageNumber($link, $pageNo).'" title="'.WCF::getLanguage()->getDynamicVariable('wcf.page.pageNo', ['pageNo' => $pageNo]).'">'.StringUtil::formatInteger($pageNo).'</a></li>'."\n";
		}
		else {
			return '<li class="active"><span>'.StringUtil::formatInteger($pageNo).'</span><span class="invisible">'.WCF::getLanguage()->getDynamicVariable('wcf.page.pagePosition', ['pageNo' => $pageNo, 'pages' => $pages]).'</span></li>'."\n";
		}
	}
	
	/**
	 * Generates HTML code for 'previous' link.
	 * 
	 * @param	string		$link
	 * @param	integer		$pageNo
	 * @return	string
	 */
	protected function makePreviousLink($link, $pageNo) {
		if ($pageNo > 1) {
			return '<li class="skip"><a href="'.$this->insertPageNumber($link, $pageNo - 1).'" title="'.WCF::getLanguage()->getDynamicVariable('wcf.global.page.previous').'" class="icon icon16 fa-chevron-left jsTooltip"></a></li>'."\n";
		}
		else {
			return '<li class="skip disabled"><span class="icon icon16 fa-chevron-left"></span></li>'."\n";
		}
	}
	
	/**
	 * Generates HTML code for 'next' link.
	 * 
	 * @param	string		$link
	 * @param	integer		$pageNo
	 * @param	integer		$pages
	 * @return	string
	 */
	protected function makeNextLink($link, $pageNo, $pages) {
		if ($pageNo && $pageNo < $pages) {
			return '<li class="skip"><a href="'.$this->insertPageNumber($link, $pageNo + 1).'" title="'.WCF::getLanguage()->getDynamicVariable('wcf.global.page.next').'" class="icon icon16 fa-chevron-right jsTooltip"></a></li>'."\n";
		}
		else {
			return '<li class="skip disabled"><span class="icon icon16 fa-chevron-right"></span></li>'."\n";
		}
	}
	
	/**
	 * @inheritDoc
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
			$parameters = [];
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
			$html .= "<nav class=\"".$this->cssClassName."\" data-link=\"".$link."\" data-pages=\"".$tagArgs['pages']."\">\n<ul>\n";
			
			// previous page
			$html .= $this->makePreviousLink($link, $tagArgs['page']);
			
			// first page
			$html .= $this->makeLink($link, 1, $tagArgs['page'], $tagArgs['pages']);
			
			// calculate page links
			$maxLinks = static::SHOW_LINKS - 4;
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
					$html .= $this->makeLink($link, 2, $tagArgs['page'], $tagArgs['pages']);
				}
				else {
					$html .= '<li class="jumpTo"><a title="'.WCF::getLanguage()->getDynamicVariable('wcf.global.page.jumpTo').'" class="jsTooltip">'.StringUtil::HELLIP.'</a></li>'."\n";
				}
			}
			
			// visible links
			for ($i = $left + 1; $i < $right; $i++) {
				$html .= $this->makeLink($link, $i, $tagArgs['page'], $tagArgs['pages']);
			}
			
			// right ... links
			if ($right < $tagArgs['pages']) {
				if ($tagArgs['pages'] - $right < 2) {
					$html .= $this->makeLink($link, $tagArgs['pages'] - 1, $tagArgs['page'], $tagArgs['pages']);
				}
				else {
					$html .= '<li class="jumpTo"><a title="'.WCF::getLanguage()->getDynamicVariable('wcf.global.page.jumpTo').'" class="jsTooltip">'.StringUtil::HELLIP.'</a></li>'."\n";
				}
			}
			
			// last page
			$html .= $this->makeLink($link, $tagArgs['pages'], $tagArgs['page'], $tagArgs['pages']);
			
			// next page
			$html .= $this->makeNextLink($link, $tagArgs['page'], $tagArgs['pages']);
			
			// close div and ul
			$html .= "</ul></nav>\n";
		}
		
		// assign html output to template var
		if (isset($tagArgs['assign'])) {
			$tplObj->assign($tagArgs['assign'], $html);
			if (!isset($tagArgs['print']) || !$tagArgs['print']) return '';
		}
		
		return $html;
	}
}
