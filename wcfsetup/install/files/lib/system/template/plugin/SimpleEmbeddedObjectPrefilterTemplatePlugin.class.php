<?php
namespace wcf\system\template\plugin;
use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * Template prefilter plugin that replaces simple embedded object placeholders. Not to be meant for
 * regular use, is currently only utilized in `wcf\data\page\content\PageContent::getParsedTemplate()`.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 * @since       3.0
 */
class SimpleEmbeddedObjectPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
		return HtmlSimpleParser::getInstance()->parseTemplate($sourceContent);
	}
}
