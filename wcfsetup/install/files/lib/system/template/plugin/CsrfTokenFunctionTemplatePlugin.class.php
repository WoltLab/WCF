<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;

/**
 * Template compiler plugin that prints the CSRF token ("Security Token").
 * 
 * Usage:
 * 	{csrfToken}
 * 	{csrfToken type=raw}
 * 	{csrfToken type=url}
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class CsrfTokenFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		if (isset($tagArgs['type']) && $tagArgs['type'] === 'raw') {
			return \wcf\system\WCF::getSession()->getSecurityToken();
		}
		else if (isset($tagArgs['type']) && $tagArgs['type'] === 'url') {
			return \rawurlencode(\wcf\system\WCF::getSession()->getSecurityToken());
		}
		else if (!isset($tagArgs['type']) || $tagArgs['type'] === 'form') {
			return sprintf('<input type="hidden" name="t" value="%s">', \wcf\system\WCF::getSession()->getSecurityToken());
		}
		else {
			throw new SystemException("Invalid type '".$tagArgs['type']."' given.");
		}
	}
}
