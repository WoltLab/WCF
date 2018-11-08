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
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	3.2
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
		$domain = Url::parse(USER_IP_ADDRESS_SEARCHENGINE ?: self::SEARCH_ENGINE_URL_DEFAULT)['host'];
		$ipAddress = StringUtil::trim(MessageUtil::stripCrap($tagArgs[0]));
		$url = sprintf(USER_IP_ADDRESS_SEARCHENGINE ?: self::SEARCH_ENGINE_URL_DEFAULT, $ipAddress);
		$title = WCF::getLanguage()->getDynamicVariable('wcf.user.ipAddress.searchEngine', ['host' => $domain, 'ipAddress' => $ipAddress]);
		
		return '<a href="' . $url . '"' . (EXTERNAL_LINK_REL_NOFOLLOW ? ' rel="nofollow"' : '') .(EXTERNAL_LINK_TARGET_BLANK ? ' target="_blank"' : '') . ' title="' . $title . '">' . $ipAddress . '</a>';
	}
}
