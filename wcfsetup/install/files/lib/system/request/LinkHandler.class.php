<?php
namespace wcf\system\request;
use wcf\data\DatabaseObjectDecorator;
use wcf\system\application\ApplicationHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles relative links within the wcf.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.request
 * @category	Community Framework
 */
class LinkHandler extends SingletonFactory {
	/**
	 * regex object to filter title
	 * @var	RegEx
	 */
	protected $titleRegex;
	
	/**
	 * title search strings
	 * @var	string[]
	 */
	protected $titleSearch = [];
	
	/**
	 * title replacement strings
	 * @var	string[]
	 */
	protected $titleReplace = [];
	
	
	/**
	 * @see	\wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->titleRegex = new Regex('[\x0-\x2F\x3A-\x40\x5B-\x60\x7B-\x7F]+');
		
		if (defined('URL_TITLE_COMPONENT_REPLACEMENT') && URL_TITLE_COMPONENT_REPLACEMENT) {
			$replacements = explode("\n", StringUtil::unifyNewlines(StringUtil::trim(URL_TITLE_COMPONENT_REPLACEMENT)));
			foreach ($replacements as $replacement) {
				if (strpos($replacement, '=') === false) continue;
				$components = explode('=', $replacement);
				$this->titleSearch[] = $components[0];
				$this->titleReplace[] = $components[1];
			}
		}
	}
	
	/**
	 * Returns a relative link.
	 * 
	 * @param	string		$controller
	 * @param	array		$parameters
	 * @param	string		$url
	 * @return	string
	 */
	public function getLink($controller = null, array $parameters = [], $url = '') {
		$abbreviation = 'wcf';
		$anchor = '';
		$isACP = $originIsACP = RequestHandler::getInstance()->isACPRequest();
		$isRaw = false;
		$encodeTitle = true;
		
		/**
		 * @deprecated 2.2 - no longer required
		 */
		$appendSession = false;
		
		// enforce a certain level of sanitation and protection for links embedded in emails
		if (isset($parameters['isEmail']) && (bool)$parameters['isEmail']) {
			$parameters['forceFrontend'] = true;
			unset($parameters['isEmail']);
		}
		
		if (isset($parameters['application'])) {
			$abbreviation = $parameters['application'];
		}
		if (isset($parameters['isRaw'])) {
			$isRaw = $parameters['isRaw'];
			unset($parameters['isRaw']);
		}
		if (isset($parameters['appendSession'])) {
			unset($parameters['appendSession']);
		}
		if (isset($parameters['isACP'])) {
			$isACP = (bool) $parameters['isACP'];
			unset($parameters['isACP']);
		}
		if (isset($parameters['forceFrontend'])) {
			if ($parameters['forceFrontend'] && $isACP) {
				$isACP = false;
			}
			unset($parameters['forceFrontend']);
		}
		if (isset($parameters['forceWCF'])) {
			/** @deprecated 2.2 */
			unset($parameters['forceWCF']);
		}
		
		if (isset($parameters['encodeTitle'])) {
			$encodeTitle = $parameters['encodeTitle'];
			unset($parameters['encodeTitle']);
		}
		
		// remove anchor before parsing
		if (($pos = strpos($url, '#')) !== false) {
			$anchor = substr($url, $pos);
			$url = substr($url, 0, $pos);
		}
		
		// build route
		if ($controller === null) {
			if ($isACP) {
				$controller = 'Index';
			}
			else {
				return '';// TODO: PageMenu::getInstance()->getLandingPage()->getProcessor()->getLink();
			}
		}
		
		// handle object
		if (isset($parameters['object'])) {
			if (!($parameters['object'] instanceof IRouteController) && $parameters['object'] instanceof DatabaseObjectDecorator && $parameters['object']->getDecoratedObject() instanceof IRouteController) {
				$parameters['object'] = $parameters['object']->getDecoratedObject();
			}
			
			if ($parameters['object'] instanceof IRouteController) {
				$parameters['id'] = $parameters['object']->getObjectID();
				$parameters['title'] = $parameters['object']->getTitle();
			}
		}
		unset($parameters['object']);
		
		if (isset($parameters['title'])) {
			// component replacement
			if (!empty($this->titleSearch)) {
				$parameters['title'] = str_replace($this->titleSearch, $this->titleReplace, $parameters['title']);
			}
			
			// remove illegal characters
			$parameters['title'] = trim($this->titleRegex->replace($parameters['title'], '-'), '-');
			
			// trim to 80 characters
			$parameters['title'] = rtrim(mb_substr($parameters['title'], 0, 80), '-');
			$parameters['title'] = mb_strtolower($parameters['title']);
			
			// encode title
			if ($encodeTitle) $parameters['title'] = rawurlencode($parameters['title']);
		}
		
		$parameters['controller'] = $controller;
		$routeURL = RouteHandler::getInstance()->buildRoute($abbreviation, $parameters, $isACP);
		if (!$isRaw && !empty($url)) {
			$routeURL .= (strpos($routeURL, '?') === false) ? '?' : '&';
		}
		
		// encode certain characters
		if (!empty($url)) {
			$url = str_replace(['[', ']'], ['%5B', '%5D'], $url);
		}
		
		$url = $routeURL . $url;
		
		// handle applications
		if (!PACKAGE_ID) {
			$url = RouteHandler::getHost() . RouteHandler::getPath(['acp']) . ($isACP ? 'acp/' : '') . $url;
		}
		else {
			if (RequestHandler::getInstance()->inRescueMode()) {
				$pageURL = RouteHandler::getHost() . str_replace('//', '/', RouteHandler::getPath(['acp']));
			}
			else {
				$pageURL = ApplicationHandler::getInstance()->getApplication($abbreviation)->getPageURL();
			}
			
			$url = $pageURL . ($isACP ? 'acp/' : '') . $url;
		}
		
		// append previously removed anchor
		$url .= $anchor;
		
		return $url;
	}
	
	/**
	 * Returns the full URL to a CMS page. The `$languageID` parameter is optional and if not
	 * present (or the integer value `-1` is given) will cause the handler to pick the correct
	 * language version based upon the user's language.
	 *
	 * Passing in an illegal page id will cause this method to fail silently, returning an
	 * empty string.
	 * 
	 * @param	integer		$pageID		page id
	 * @param	integer		$languageID	language id, optional
	 * @return	string		full URL of empty string if `$pageID` is invalid
	 * @since	2.2
	 */
	public function getCmsLink($pageID, $languageID = -1) {
		// use current language
		if ($languageID === -1) {
			$data = ControllerMap::getInstance()->lookupCmsPage($pageID, WCF::getLanguage()->languageID);
			
			// no result
			if ($data === null) {
				// attempt to use the default language instead
				if (LanguageFactory::getInstance()->getDefaultLanguageID() != WCF::getLanguage()->languageID) {
					$data = ControllerMap::getInstance()->lookupCmsPage($pageID, LanguageFactory::getInstance()->getDefaultLanguageID());
				}
				
				// no result, possibly this is a non-multilingual page
				if ($data === null) {
					$data = ControllerMap::getInstance()->lookupCmsPage($pageID, null);
				}
				
				// still no result, page does not exist at all
				if ($data === null) {
					return '';
				}
			}
		}
		else {
			$data = ControllerMap::getInstance()->lookupCmsPage($pageID, $languageID);
			
			// no result, page does not exist or at least not in the given language
			if ($data === null) {
				return '';
			}
		}
		
		return $this->getLink($data['controller'], [
			'application' => $data['application'],
			'forceFrontend' => true
		]);
	}
}
