<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * See CsrfTokenFunctionTemplatePlugin.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class CsrfTokenPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
		$getToken = '$__wcf->session->getSecurityToken()';
		
		return strtr($sourceContent, [
			'{csrfToken type=raw}' => sprintf('{@%s}', $getToken),
			'{csrfToken type=url}' => sprintf('{@%s|rawurlencode}', $getToken),
			'{csrfToken}' => sprintf('<input type="hidden" name="t" value="{@%s}">', $getToken),
		]);
	}
}
