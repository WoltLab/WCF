<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * The 'hascontent' prefilter inserts ability to insert code dynamically upon the
 * contents of 'content'.
 * 
 * Usage:
 *	{hascontent}
 *	<ul>
 *		{content}
 *			{if $foo}<li>bar</li>{/if}
 *		{/content}
 *	</ul>
 *	{hascontentelse}
 *		<p>baz</p>
 *	{/hascontent}
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template.plugin
 * @category	Community Framework
 */
class HascontentPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @see	wcf\system\template\IPrefilterTemplatePlugin::execute()
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
		$ldq = preg_quote($compiler->getLeftDelimiter(), '~');
		$rdq = preg_quote($compiler->getRightDelimiter(), '~');
		
		$sourceContent = preg_replace_callback("~{$ldq}hascontent{$rdq}(.*){$ldq}content{$rdq}(.*){$ldq}\/content{$rdq}(.*)({$ldq}hascontentelse{$rdq}(.*))?{$ldq}\/hascontent{$rdq}~sU", array('self', 'replaceContentCallback'), $sourceContent);
		
		return $sourceContent;
	}
	
	/**
	 * Reorders content to provide a logical order. In fact the content of
	 * '{content}' is moved outside the if-condition in order to capture
	 * the content during runtime, safely determining wether content is empty
	 * or not.
	 * 
	 * @param	array		$matches
	 * @return	string
	 */
	protected static function replaceContentCallback(array $matches) {
		$beforeContent = $matches[1];
		$content = $matches[2];
		$afterContent = $matches[3];
		$elseContent = (isset($matches[5])) ? $matches[5] : '';
		
		$variable = 'hascontent_' . StringUtil::getRandomID();
		
		$newContent = '{capture assign='.$variable.'}'.$content.'{/capture}'."\n";
		$newContent .= '{assign var='.$variable.' value=$'.$variable.'|trim}'."\n";
		$newContent .= '{if $'.$variable.'}'.$beforeContent.'{@$'.$variable.'}'."\n".$afterContent;
		
		if (!empty($elseContent)) {
			$newContent .= '{else}'.$elseContent."\n";
		}
		
		$newContent .= '{/if}'."\n";
		
		return $newContent;
	}
}
