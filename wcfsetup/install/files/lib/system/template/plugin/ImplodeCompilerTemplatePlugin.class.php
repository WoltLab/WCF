<?php
namespace wcf\system\template\plugin;
use wcf\system\exception\SystemException;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\util\StringUtil;

/**
 * Template compiler plugin which joins array elements to a string.
 * 
 * Usage:
 * 	{implode from=$array key=bar item=foo glue=";"}{$foo}{/implode}
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template\Plugin
 */
class ImplodeCompilerTemplatePlugin implements ICompilerTemplatePlugin {
	/**
	 * local tag stack
	 * @var	string[]
	 */
	protected $tagStack = [];
	
	/**
	 * @inheritDoc
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('implode');
		
		if (!isset($tagArgs['from'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'from' argument in implode tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
		if (!isset($tagArgs['item'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'item' argument in implode tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()));
		}
		
		$hash = StringUtil::getRandomID();
		$glue = isset($tagArgs['glue']) ? $tagArgs['glue'] : "', '";
		$this->tagStack[] = ['hash' => $hash, 'glue' => $glue];
		
		$phpCode = "<?php\n";
		$phpCode .= "\$_length".$hash." = count(".$tagArgs['from'].");\n";
		$phpCode .= "\$_i".$hash." = 0;\n";
		$phpCode .= "foreach (".$tagArgs['from']." as ".(isset($tagArgs['key']) ? (mb_substr($tagArgs['key'], 0, 1) != '$' ? "\$this->v[".$tagArgs['key']."]" : $tagArgs['key'])." => " : '').(mb_substr($tagArgs['item'], 0, 1) != '$' ? "\$this->v[".$tagArgs['item']."]" : $tagArgs['item']).") { ?>";
		return $phpCode;
	}
	
	/**
	 * @inheritDoc
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('implode');
		$tagArgs = array_pop($this->tagStack);
		
		$phpCode = "<?php\n";
		$phpCode .= "if (++\$_i".$tagArgs['hash']." < \$_length".$tagArgs['hash'].") { echo ".$tagArgs['glue']."; }\n";
		$phpCode .= "} ?>";
		
		return $phpCode;
	}
}
