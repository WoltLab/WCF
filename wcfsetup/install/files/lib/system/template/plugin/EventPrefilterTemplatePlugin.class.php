<?php
namespace wcf\system\template\plugin;
use wcf\system\template\IPrefilterTemplatePlugin;
use wcf\system\template\TemplateScriptingCompiler;

/**
 * The 'event' prefilter inserts template listener's code before compilation.
 * 
 * Usage:
 * {event name='foo'}
 *
 * @author 	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class EventPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @see wcf\system\template\IPrefilterTemplatePlugin::execute()
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
		$ldq = preg_quote($compiler->getLeftDelimiter(), '~');
		$rdq = preg_quote($compiler->getRightDelimiter(), '~');
		$sourceContent = preg_replace("~{$ldq}event\ name\=\'([\w]+)\'{$rdq}~e", 'wcf\system\WCF::getTPL()->getTemplateListenerCode(\''.$templateName.'\', \'$1\')', $sourceContent);
		
		return $sourceContent;
	}
}
