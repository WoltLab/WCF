<?php
namespace wcf\system\template\plugin;
use wcf\system\application\ApplicationHandler;
use wcf\system\request\RouteHandler;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template function plugin which generates attributes for `a` HTML elements.
 *
 * Required parameter:
 *      `url` (string)
 * Optional parameter:
 *      `appendHref` (bool, default true)
 *      `appendClassname` (bool, default true)
 *      `isUgc` (bool, default false)
 *
 * Usage:
 *      {anchorAttributes url=$url}
 *
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	5.3
 */
class AnchorAttributesFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (empty($tagArgs['url'])) {
			throw new \InvalidArgumentException("Missing 'url' attribute.");
		}
		$url = $tagArgs['url'];
		$appendClassname = $tagArgs['appendClassname'] ?? true;
		$appendHref = $tagArgs['appendHref'] ?? true;
		$isUgc = $tagArgs['isUgc'] ?? false;
		
		$external = true;
		if (ApplicationHandler::getInstance()->isInternalURL($url)) {
			$external = false;
			$url = preg_replace('~^https?://~', RouteHandler::getProtocol(), $url);
		}
		
		$attributes = '';
		if ($appendHref) {
			$attributes .= ' href="' . StringUtil::encodeHTML($url) . '"';
		}
		
		if ($external) {
			if ($appendClassname) {
				$attributes .= ' class="externalURL"';
			}
			
			$rel = 'nofollow';
			if (EXTERNAL_LINK_TARGET_BLANK) {
				$rel .= ' noopener noreferrer';
				$attributes .= ' target="_blank"';
			}
			if ($isUgc) {
				$rel .= ' ugc';
			}
			
			$attributes .= ' rel="' . $rel . '"';
		}
		
		return StringUtil::trim($attributes);
	}
}
