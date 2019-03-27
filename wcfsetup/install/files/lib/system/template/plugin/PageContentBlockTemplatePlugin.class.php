<?php
namespace wcf\system\template\plugin;
use wcf\data\page\Page;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\template\TemplateEngine;

/**
 * Template block plugin which displays the content of a given CMS page.
 *
 * The unique identifier and the `pageID` attribute as well as the `language` and
 * `languageID` attribute are mutually exclusive. Combining these values will not
 * cause this plugin to fail, but instead `pageID` and `languageID` will be considered
 * to be of higher specificity and the unique identifier / `language` attribute will
 * be ignored.
 *
 * Usage, language is automatically resolved:
 *      {pageContent}com.woltlab.wcf.CookiePolicy{/pageContent}
 *
 * `pageID` attribute instead of unique identifier:
 *      {pageContent pageID=1}{/pageContent}
 *
 * `language` attribute to force a localized version:
 *      {pageContent language='de'}com.woltlab.wcf.CookiePolicy{/pageContent}
 *
 * `languageID` attribute similar to the `language` attribute:
 *      {pageContent languageID=2}com.woltlab.wcf.CookiePolicy{/pageContent}
 *
 * @author	Alexander Ebert, Christopher Walz
 * @see         \wcf\system\template\plugin\PageBlockTempaltePlugin
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class PageContentBlockTemplatePlugin implements IBlockTemplatePlugin {
	/**
	 * internal loop counter
	 * @var	integer
	 */
	protected $counter = 0;

	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, $blockContent, TemplateEngine $tplObj) {
		$page = null;

		if (!empty($tagArgs['pageID'])) {
			$pageID = intval($tagArgs['pageID']);
			$page = new Page($pageID);
		}
		else if (!empty($blockContent)) {
			$page = Page::getPageByIdentifier($blockContent);
		}

		if (!$page->pageID) {
			throw new SystemException("Missing 'pageID' attribute or unique identifier or page couldn't be found.");
		}

		$languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
		if (!empty($tagArgs['languageID'])) {
			$languageID = intval($tagArgs['languageID']);
		}
		else if (!empty($tagArgs['language'])) {
			$language = LanguageFactory::getInstance()->getLanguageByCode($tagArgs['language']);
			if ($language !== null) $languageID = $language->languageID;
		}

		$pageContent = $page->getPageContentByLanguage($languageID);

		if ($pageContent === null) {
			return '';
		} else {
			if ($page->pageType == 'text') {
				$formattedContent = $pageContent->getFormattedContent();
				return '<div class="section cmsContent htmlContent">' . $formattedContent . '</div>';
			} else if ($page->pageType == 'html') {
				return $pageContent->getParsedContent();
			} else if ($page->pageType == 'tpl') {
				return $pageContent->getParsedTemplate($pageContent);
			}
		}

		return '';
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
