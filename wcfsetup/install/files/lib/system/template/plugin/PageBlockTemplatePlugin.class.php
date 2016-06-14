<?php
namespace wcf\system\template\plugin;
use wcf\data\page\Page;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template block plugin which generates a link to a CMS page.
 *
 * The unique identifier and the `pageID` attribute as well as the `language` and
 * `languageID` attribute are mutually exclusive. Combining these values will not
 * cause this plugin to fail, but instead `pageID` and `languageID` will be considered
 * to be of higher specificity and the unique identifier / `language` attribute will
 * be ignored.
 * 
 * Usage, language is automatically resolved:
 *      {page}com.woltlab.wcf.CookiePolicy{/page}
 * 
 * `pageID` attribute instead of unique identifier:
 *      {page pageID=1}{/page}
 * 
 * `language` attribute to force a localized version:
 *      {page language='de'}com.woltlab.wcf.CookiePolicy{/page}
 * 
 * `languageID` attribute similar to the `language` attribute:
 *      {page languageID=2}com.woltlab.wcf.CookiePolicy{/page}
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	3.0
 */
class PageBlockTemplatePlugin implements IBlockTemplatePlugin {
	/**
	 * internal loop counter
	 * @var	integer
	 */
	protected $counter = 0;
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, $blockContent, TemplateEngine $tplObj) {
		$pageID = null;
		if (!empty($tagArgs['pageID'])) {
			$pageID = intval($tagArgs['pageID']);
		}
		else if (!empty($blockContent)) {
			$page = Page::getPageByIdentifier($blockContent);
			$pageID = ($page) ? $page->pageID : 0;
		}
		
		if ($pageID === null) {
			throw new SystemException("Missing 'pageID' attribute or unique identifier.");
		}
		
		$languageID = -1;
		if (!empty($tagArgs['languageID'])) {
			$languageID = intval($tagArgs['languageID']);
		}
		else if (!empty($tagArgs['language'])) {
			$language = LanguageFactory::getInstance()->getLanguageByCode($tagArgs['language']);
			if ($language !== null) $languageID = $language->languageID;
		}
		
		$link = LinkHandler::getInstance()->getCmsLink($pageID, $languageID);
		if (!empty($tagArgs['encode'])) {
			return StringUtil::encodeHTML($link);
		}
		
		return $link;
	}
	
	/**
	 * @inheritDoc
	 */
	public function init($tagArgs, TemplateEngine $tplObj) {
		$this->counter = 0;
	}
	
	/**
	 * @inheritDoc
	 */
	public function next(TemplateEngine $tplObj) {
		if ($this->counter == 0) {
			$this->counter++;
			return true;
		}
		
		return false;
	}
}
