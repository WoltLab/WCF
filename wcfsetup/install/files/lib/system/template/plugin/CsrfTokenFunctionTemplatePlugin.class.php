<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;

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
		$token = WCF::getSession()->getSecurityToken();
		$type = $tagArgs['type'] ?? 'form';
		
		switch ($type) {
			case 'raw':
				return $token;
			case 'url':
				return \rawurlencode($token);
			case 'form':
				return \sprintf('<input type="hidden" name="t" value="%s">', $token);
			default:
				throw new SystemException("Invalid type '".$type."' given.");
		}
	}
}
