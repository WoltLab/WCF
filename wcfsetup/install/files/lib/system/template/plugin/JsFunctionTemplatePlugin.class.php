<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\template\TemplateEngine;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Template function plugin which script tags. File extension is automatically added
 * to the script source and MUST NOT be provided.
 * 
 * If ENABLE_DEBUG_MODE=0 then the extension is '.min.js', don't fail to provide it.
 * 
 * Usage:
 * 	{js application='wbb' file='WBB'}
 * 	http://example.com/js/WBB.js
 * 	
 * 	{js application='wcf' file='WCF.Like' bundle='WCF.Combined'}
 * 	http://example.com/wcf/js/WCF.Like.js (ENABLE_DEBUG_MODE=1)
 * 	http://example.com/wcf/js/WCF.Combined.min.js (ENABLE_DEBUG_MODE=0)
 * 	
 * 	{js application='wcf' lib='jquery'}
 * 	http://example.com/wcf/js/3rdParty/jquery.js
 * 	
 * 	{js application='wcf' lib='jquery-ui' file='awesomeWidget'}
 * 	http://example.com/wcf/js/3rdParty/jquery-ui/awesomeWidget.js
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since	3.0
 */
class JsFunctionTemplatePlugin implements IFunctionTemplatePlugin {
	/**
	 * list of already included JavaScript files
	 * @var	string[]
	 */
	protected $includedFiles = [];
	
	/**
	 * @inheritDoc
	 */
	public function execute($tagArgs, TemplateEngine $tplObj) {
		// needed arguments: application and lib/file
		if (empty($tagArgs['application'])) throw new SystemException("missing 'application' argument in js tag");
		if (empty($tagArgs['file']) && empty($tagArgs['lib'])) throw new SystemException("missing 'file' or 'lib' argument in js tag");
		
		$isJqueryUi = false;
		if (isset($tagArgs['lib']) && $tagArgs['lib'] === 'jquery-ui' && empty($tagArgs['file'])) {
			$tagArgs['bundle'] = '';
			$isJqueryUi = true;
		}
		
		$src = WCF::getPath($tagArgs['application']) . (isset($tagArgs['acp']) && $tagArgs['acp'] === 'true' ? 'acp/' : '') . 'js/';
		if (!empty($tagArgs['bundle']) && !ENABLE_DEBUG_MODE) {
			$src .= $tagArgs['bundle'];
		}
		else if (!empty($tagArgs['lib'])) {
			if ($isJqueryUi) {
				$src .= (ENABLE_DEBUG_MODE) ? '3rdParty/' . $tagArgs['lib'] : 'WCF.Combined';
			}
			else {
				$src .= '3rdParty/' . $tagArgs['lib'];
				if (!empty($tagArgs['file'])) {
					$src .= '/' . $tagArgs['file'];
				}
			}
		}
		else {
			$src .= $tagArgs['file'];
		}
		
		if (isset($this->includedFiles[$src])) {
			return '';
		}
		
		$this->includedFiles[$src] = true;
		$src .= (!ENABLE_DEBUG_MODE ? '.min' : '') . '.js?v=' . LAST_UPDATE_TIME;
		
		$relocate = !RequestHandler::getInstance()->isACPRequest() && (!isset($tagArgs['core']) || $tagArgs['core'] !== 'true');
		$html = '<script' . ($relocate ? ' data-relocate="true"' : '') . ' src="' . $src . '"></script>'."\n";
		
		if (isset($tagArgs['encodeJs']) && $tagArgs['encodeJs'] === 'true') {
			$html = StringUtil::encodeJS($html);
		}
		
		return $html;
	}
}
