<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * IP address search modifier plugin which links the IP address to a search engine.
 * 
 * Usage:
 * 	{$ipAddress|ipSearch}
 * 	{"127.0.0.1"|ipSearch}
 * 
 * @author	Florian Gail
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	5.2
 */
class IpSearchModifierTemplatePlugin implements IModifierTemplatePlugin {
	/**
	 * default search engine schema
	 * @var string
	 */
	const SEARCH_ENGINE_URL_DEFAULT = 'https://www.google.com/search?q=%s';
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		$domain = Url::parse(IP_ADDRESS_SEARCH_ENGINE ?: self::SEARCH_ENGINE_URL_DEFAULT)['host'];
		$ipAddress = StringUtil::trim(MessageUtil::stripCrap($tagArgs[0]));
		$url = sprintf(IP_ADDRESS_SEARCH_ENGINE ?: self::SEARCH_ENGINE_URL_DEFAULT, $ipAddress);
		$title = WCF::getLanguage()->getDynamicVariable('wcf.user.ipAddress.searchEngine', ['host' => $domain, 'ipAddress' => $ipAddress]);
		
		return '<a '. StringUtil::getAnchorTagAttributes($url) .' title="' . $title . '">' . $ipAddress . '</a>';
	}
}
