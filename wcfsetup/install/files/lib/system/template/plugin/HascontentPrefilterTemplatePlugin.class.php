<?php
namespace wcf\system\template\plugin;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * Template prefiler plugin which allows inserting code dynamically upon the contents
 * of 'content'.
 * 
 * Usage:
 * 	{hascontent}
 * 	<ul>
 * 		{content}
 * 			{if $foo}<li>bar</li>{/if}
 * 		{/content}
 * 	</ul>
 * 	{hascontentelse}
 * 		<p>baz</p>
 * 	{/hascontent}
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class HascontentPrefilterTemplatePlugin implements IPrefilterTemplatePlugin {
	/**
	 * @inheritDoc
	 */
	public function execute($templateName, $sourceContent, TemplateScriptingCompiler $compiler) {
		$ldq = preg_quote($compiler->getLeftDelimiter(), '~');
		$rdq = preg_quote($compiler->getRightDelimiter(), '~');
		
		$sourceContent = preg_replace_callback("~{$ldq}hascontent( assign='(?P<assign>.*)')?{$rdq}(?P<before>.*){$ldq}content{$rdq}(?P<content>.*){$ldq}\/content{$rdq}(?P<after>.*)({$ldq}hascontentelse{$rdq}(?P<else>.*))?{$ldq}\/hascontent{$rdq}~sU", ['self', 'replaceContentCallback'], $sourceContent);
		
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
		$beforeContent = $matches['before'];
		$content = $matches['content'];
		$afterContent = $matches['after'];
		$elseContent = (isset($matches['else'])) ? $matches['else'] : '';
		$assignContent = (isset($matches['assign']) && !empty($matches['assign'])) ? $matches['assign'] : '';
		$variable = 'hascontent_' . StringUtil::getRandomID();
		
		$newContent = '{capture assign='.$variable.'}'.$content.'{/capture}'."\n";
		$newContent .= '{assign var='.$variable.' value=$'.$variable.'|trim}'."\n";
		
		if ($assignContent) $newContent .= '{capture assign='.$assignContent.'}'."\n";
		$newContent .= '{if $'.$variable.'}'.$beforeContent.'{@$'.$variable.'}'."\n".$afterContent;
		
		if (!empty($elseContent)) {
			$newContent .= '{else}'.$elseContent."\n";
		}
		
		$newContent .= '{/if}'."\n";
		
		if ($assignContent) $newContent .= "{/capture}\n{@$".$assignContent."}\n";
		
		return $newContent;
	}
}
