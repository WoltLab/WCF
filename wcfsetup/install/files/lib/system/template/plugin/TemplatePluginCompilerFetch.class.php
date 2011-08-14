<?php
namespace wcf\system\template\plugin;
use wcf\system\template\ITemplatePluginCompiler;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\exception\SystemException;

/**
 * The 'fetch' compiler function fetches files from the local file system, http, or ftp and displays the content.
 * 
 * Usage:
 * {fetch file='x.html'}
 * {fetch file='x.html' assign=var}
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category 	Community Framework
 */
class TemplatePluginCompilerFetch implements ITemplatePluginCompiler {
	/**
	 * @see wcf\system\template\ITemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		if (!isset($tagArgs['file'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'file' argument in fetch tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
		
		if (isset($tagArgs['assign'])) {
			return "<?php \$this->assign(".$tagArgs['assign'].", @file_get_contents(".$tagArgs['file'].")); ?>";
		}
		else {
			return "<?php echo @file_get_contents(".$tagArgs['file']."); ?>";
		}
	}
	
	/**
	 * @see wcf\system\template\ITemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		throw new SystemException($compiler->formatSyntaxError("unknown tag {/fetch}", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
	}
}
