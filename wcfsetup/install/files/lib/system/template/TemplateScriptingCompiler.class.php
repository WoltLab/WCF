<?php
namespace wcf\system\template;
use wcf\system\exception\SystemException;
use wcf\system\template\plugin\ICompilerTemplatePlugin;
use wcf\system\template\plugin\IPrefilterTemplatePlugin;
use wcf\util\StringStack;
use wcf\util\StringUtil;

/**
 * TemplateScriptingCompiler compiles template source in valid php code.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.template
 * @category 	Community Framework
 */
class TemplateScriptingCompiler {
	/**
	 * template engine object
	 * @var	wcf\system\templateTemplateEngine
	 */
	protected $template;
	
	/**
	 * PHP functions that can be used in the modifier syntax and are unknown
	 * to the function_exists PHP method
	 * @var	array<string>
	 */
	protected $unknownPHPFunctions = array('isset', 'unset', 'empty');
	
	/**
	 * PHP functions that can not be used in the modifier syntax
	 * @var	array<string>
	 */
	protected $disabledPHPFunctions = array(
		'system', 'exec', 'passthru', 'shell_exec', // command line execution
		'include', 'require', 'include_once', 'require_once', // includes
		'eval', 'virtual', 'call_user_func_array', 'call_user_func', 'assert' // code execution
	);
	
	/**
	 * pattern to match variable operators like -> or .
	 * @var	string
	 */
	protected $variableOperatorPattern;
	
	/**
	 * pattern to match condition operators like == or <
	 * @var	string
	 */
	protected $conditionOperatorPattern;
	
	/**
	 * negative lookbehind for a backslash
	 * @var	string
	 */
	protected $escapedPattern;
	
	/**
	 * pattern to match valid variable names
	 * @var	string
	 */
	protected $validVarnamePattern;
	
	/**
	 * pattern to match constants like CONSTANT or __CONSTANT
	 * @var	string
	 */
	protected $constantPattern;
	
	/**
	 * pattern to match double quoted strings like "blah" or "quote: \"blah\""
	 * @var	string
	 */
	protected $doubleQuotePattern;
	
	/**
	 * pattern to match single quoted strings like 'blah' or 'don\'t'
	 * @var	string
	 */
	protected $singleQuotePattern;
	
	/**
	 * pattern to match single or double quoted strings
	 * @var	string
	 */
	protected $quotePattern;
	
	/**
	 * pattern to match numbers, true, false and null
	 * @var	string
	 */
	protected $numericPattern;
	
	/**
	 * pattern to match simple variables like $foo
	 * @var	string
	 */
	protected $simpleVarPattern;
	
	/**
	 * pattern to match outputs like @$foo or #CONST
	 * @var	string
	 */
	protected $outputPattern;
	
	/**
	 * identifier of currently compiled template
	 * @var	string
	 */
	protected $currentIdentifier;
	
	/**
	 * current line number during template compilation
	 * @var	string
	 */
	protected $currentLineNo;
	
	protected $modifiers = array();
	
	/**
	 * list of automatically loaded tenplate plugins
	 * @var	array<string>
	 */
	protected $autoloadPlugins = array();
	
	/**
	 * stack with template tags data
	 * @var	array
	 */
	protected $tagStack = array();
	
	/**
	 * list of loaded compiler plugin objects
	 * @var	array<wcf\system\template\ICompilerTemplatePlugin>
	 */
	protected $compilerPlugins = array();
	
	/**
	 * stack used to compile the capture tag
	 * @var	array
	 */
	protected $captureStack = array();
	
	/**
	 * left delimiter of template syntax
	 * @var	string
	 */
	protected $leftDelimiter = '{';
	
	/**
	 * right delimiter of template syntax
	 * @var	string
	 */
	protected $rightDelimiter = '}';
	
	/**
	 * left delimiter of template syntax used in regular expressions
	 * @var	string
	 */
	protected $ldq;
	
	/**
	 * right delimiter of template syntax used in regular expressions
	 * @var	string
	 */
	protected $rdq;
	
	/**
	 * Creates a new TemplateScriptingCompiler object.
	 * 
	 * @param	wcf\system\templateTemplateEngine	$template
	 */
	public function __construct(TemplateEngine $template) {
		$this->template = $template;
		
		// quote left and right delimiter for use in regular expressions
		$this->ldq = preg_quote($this->leftDelimiter, '~').'(?=\S)';
		$this->rdq = '(?<=\S)'.preg_quote($this->rightDelimiter, '~');
		
		// build regular expressions
		$this->buildPattern();
	}
	
	/**
	 * Compiles the source of a template.
	 * 
	 * @param	string		$identifier
	 * @param	string		$sourceContent
	 * @return	string
	 */
	public function compileString($identifier, $sourceContent) {
		// reset vars
		$this->autoloadPlugins = $this->tagStack = $this->stringStack = $this->literalStack = array();
		$this->currentIdentifier = $identifier;
		$this->currentLineNo = 1;
		
		// apply prefilters
		$sourceContent = $this->applyPrefilters($identifier, $sourceContent);
		
		// replace all {literal} Tags with unique hash values
		$sourceContent = $this->replaceLiterals($sourceContent);
		
		// handle <?php tags
		$sourceContent = $this->replacePHPTags($sourceContent);
		
		// remove comments
		$sourceContent = $this->removeComments($sourceContent);
		
		// match all template tags
		$matches = array();
		preg_match_all("~".$this->ldq."(.*?)".$this->rdq."~s", $sourceContent, $matches);
		$templateTags = $matches[1];
		
		// Split content by template tags to obtain non-template content
		$textBlocks = preg_split("~".$this->ldq.".*?".$this->rdq."~s", $sourceContent);
		
		// compile the template tags into php-code
		$compiledTags = array();
		for ($i = 0, $j = count($templateTags); $i < $j; $i++) {
			$this->currentLineNo += StringUtil::countSubstring($textBlocks[$i], "\n");
			$compiledTags[] = $this->compileTag($templateTags[$i]);
			$this->currentLineNo += StringUtil::countSubstring($templateTags[$i], "\n");
		}
		
		// throw error messages for unclosed tags
		if (count($this->tagStack) > 0) {
			foreach ($this->tagStack as $tagStack) {
				throw new SystemException($this->formatSyntaxError('unclosed tag {'.$tagStack[0].'}', $this->currentIdentifier, $tagStack[1]));
			}
			return false;
		}
		
		$compiledContent = '';
		// Interleave the compiled contents and text blocks to get the final result.
		for ($i = 0, $j = count($compiledTags); $i < $j; $i++) {
			if ($compiledTags[$i] == '') {
				// tag result empty, remove first newline from following text block
				$textBlocks[$i + 1] = preg_replace('%^(\r\n|\r|\n)%', '', $textBlocks[$i + 1]);
			}
			$compiledContent .= $textBlocks[$i].$compiledTags[$i];
		}
		$compiledContent .= $textBlocks[$i];
		$compiledContent = chop($compiledContent);
		
		// INSERT POSTFILTERS HERE!
		
		// reinsert {literal} Tags
		$compiledContent = $this->reinsertLiterals($compiledContent);
		
		// include Plugins
		$compiledAutoloadPlugins = '';
		if (count($this->autoloadPlugins) > 0) {
			$compiledAutoloadPlugins = "<?php\n";
			foreach ($this->autoloadPlugins as $className/* => $fileName*/) {
				$compiledAutoloadPlugins .= "use ".$className.";\n";
				$compiledAutoloadPlugins .= "if (!isset(\$this->pluginObjects['$className'])) {\n";
				/*
				if (WCF_DIR != '' && strpos($fileName, WCF_DIR) === 0) {
					$compiledAutoloadPlugins .= "require_once(WCF_DIR.'".StringUtil::replace(WCF_DIR, '', $fileName)."');\n";
				}
				else {
					$compiledAutoloadPlugins .= "require_once('".$fileName."');\n";
				}
				*/
				$compiledAutoloadPlugins .= "\$this->pluginObjects['$className'] = new $className;\n";
				$compiledAutoloadPlugins .= "}\n";
			}
			$compiledAutoloadPlugins .= "?>";
		}
		
		return $compiledAutoloadPlugins.$compiledContent;
	}
	
	/**
	 * Compiles a template tag.
	 * 
	 * @param	string		$tag
	 */
	protected function compileTag($tag) {
		if (preg_match('~^'.$this->outputPattern.'~s', $tag)) {
			// variable output
			return $this->compileOutputTag($tag);
		}
		
		$match = array();
		// replace 'else if' with 'elseif'
		$tag = preg_replace('~^else\s+if(?=\s)~i', 'elseif', $tag);
		
		if (preg_match('~^(/?\w+)~', $tag, $match)) {
			// build in function or plugin
			$tagCommand = $match[1];
			$tagArgs = StringUtil::substring($tag, StringUtil::length($tagCommand));
			
			switch ($tagCommand) {
				case 'if':
					$this->pushTag('if');
					return $this->compileIfTag($tagArgs);
					
				case 'elseif':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'if' && $openTag != 'elseif') {
						throw new SystemException($this->formatSyntaxError('unxepected {elseif}', $this->currentIdentifier, $this->currentLineNo));
					}
					else if ($openTag == 'if') {
						$this->pushTag('elseif');
					}
					return $this->compileIfTag($tagArgs, true);
					
				case 'else':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'if' && $openTag != 'elseif') {
						throw new SystemException($this->formatSyntaxError('unexpected {else}', $this->currentIdentifier, $this->currentLineNo));
					}
					else {
						$this->pushTag('else');
						return '<?php } else { ?>';
					}
					
				case '/if':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'if' && $openTag != 'elseif' && $openTag != 'else') {
						throw new SystemException($this->formatSyntaxError('unexpected {/if}', $this->currentIdentifier, $this->currentLineNo));
					}
					else {
						$this->popTag('if');
					}
					return '<?php } ?>';
				
				case 'include':
					return $this->compileIncludeTag($tagArgs);
					
				case 'foreach':
					$this->pushTag('foreach');
					return $this->compileForeachTag($tagArgs);

				case 'foreachelse':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'foreach') {
						throw new SystemException($this->formatSyntaxError('unexpected {foreachelse}', $this->currentIdentifier, $this->currentLineNo));
					}
					else {
						$this->pushTag('foreachelse');
						return '<?php } } else { { ?>';
					}
					
				case '/foreach':
					$this->popTag('foreach');
					return "<?php } } ?>";
					
				case 'section':
					$this->pushTag('section');
					return $this->compileSectionTag($tagArgs);
			
				case 'sectionelse':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'section') {
						throw new SystemException($this->formatSyntaxError('unexpected {sectionelse}', $this->currentIdentifier, $this->currentLineNo));
					}
					else {
						$this->pushTag('sectionelse');
						return '<?php } } else { { ?>';
					}
					
				case '/section':
					$this->popTag('section');
					return "<?php } } ?>";
					
				case 'capture':
					$this->pushTag('capture');
					return $this->compileCaptureTag(true, $tagArgs);
			
				case '/capture':
					$this->popTag('capture');
					return $this->compileCaptureTag(false);
					
				case 'ldelim':
					return $this->leftDelimiter;
					
				case 'rdelim':
					return $this->rightDelimiter;
				
				default:
					// 1) compiler functions first
					if ($phpCode = $this->compileCompilerPlugin($tagCommand, $tagArgs)) {
						return $phpCode;
					}
					// 2) block functions
					if ($phpCode = $this->compileBlockPlugin($tagCommand, $tagArgs)) {
						return $phpCode;
					}
					// 3) functions
					if ($phpCode = $this->compileFunctionPlugin($tagCommand, $tagArgs)) {
						return $phpCode;
					}
			}
		}
		
		throw new SystemException($this->formatSyntaxError('unknown tag {'.$tag.'}', $this->currentIdentifier, $this->currentLineNo));
	}
	
	/**
	 * Compiles a function plugin.
	 *
	 * @param 	string 		$tagCommand
	 * @param 	string 		$tagArgs
	 * @return 	mixed				false, if the plugin does not exist
	 * 						otherwise the php output of the plugin
	 */
	protected function compileFunctionPlugin($tagCommand, $tagArgs) {
		$className = $this->template->getPluginClassName('function', $tagCommand);
		if (!class_exists($className)) {
			return false;
		}
		$this->autoloadPlugins[$className] = $className;
		
		$tagArgs = $this->makeArgString($this->parseTagArgs($tagArgs, $tagCommand));

		return "<?php echo \$this->pluginObjects['".$className."']->execute(array(".$tagArgs."), \$this); ?>";
	}
	
	/**
	 * Compiles a block plugin.
	 *
	 * @param 	string 		$tagCommand
	 * @param 	string 		$tagArgs
	 * @return 	mixed				false, if the plugin does not exist
	 * 						otherwise the php output of the plugin
	 */
	protected function compileBlockPlugin($tagCommand, $tagArgs) {
		// check wheater this is the start ({block}) or the
		// end tag ({/block})
		if (substr($tagCommand, 0, 1) == '/') {
			$tagCommand = substr($tagCommand, 1);
			$startTag = false;
		}
		else {
			$startTag = true;
		}

		$className = $this->template->getPluginClassName('block', $tagCommand);
		if (!class_exists($className)) {
			return false;
		}
		$this->autoloadPlugins[$className] = $className;
		
		if ($startTag) {
			$this->pushTag($tagCommand);

			$tagArgs = $this->makeArgString($this->parseTagArgs($tagArgs, $tagCommand));

			$phpCode = "<?php \$this->tagStack[] = array('".$tagCommand."', array(".$tagArgs."));\n";
			$phpCode .= "\$this->pluginObjects['".$className."']->init(\$this->tagStack[count(\$this->tagStack) - 1][1], \$this);\n";
			$phpCode .= "while (\$this->pluginObjects['".$className."']->next(\$this)) { ob_start(); ?>";
		}
		else {
			$this->popTag($tagCommand);
			$phpCode = "<?php echo \$this->pluginObjects['".$className."']->execute(\$this->tagStack[count(\$this->tagStack) - 1][1], ob_get_clean(), \$this); }\n";
			$phpCode .= "array_pop(\$this->tagStack);\n";
			$phpCode .= "unset(\$blockContent, \$blockRepeat); ?>";
		}
		
		return $phpCode;
	}
	
	
	/**
	 * Compiles a compiler function/block.
	 *
	 * @param 	string 		$tagCommand
	 * @param 	string 		$tagArgs
	 * @return 	mixed				false, if the plugin does not exist
	 * 						otherwise the php output of the plugin
	 */
	protected function compileCompilerPlugin($tagCommand, $tagArgs) {
		// check wheater this is the start ({block}) or the
		// end tag ({/block})
		if (substr($tagCommand, 0, 1) == '/') {
			$tagCommand = substr($tagCommand, 1);
			$startTag = false;
		}
		else {
			$startTag = true;
		}

		$className = $this->template->getPluginClassName('compiler', $tagCommand);
		// if necessary load plugin from plugin-dir
		if (!isset($this->compilerPlugins[$className])) {
			if (!class_exists($className)) {
				return false;
			}
			
			$this->compilerPlugins[$className] = new $className();
			
			if (!($this->compilerPlugins[$className] instanceof ICompilerTemplatePlugin)) {
				throw new SystemException($this->formatSyntaxError("Compiler plugin '".$tagCommand."' does not implement the interface 'ICompilerTemplatePlugin'", $this->currentIdentifier));
			}
		}
		
		// execute plugin
		if ($startTag) {
			$tagArgs = $this->parseTagArgs($tagArgs, $tagCommand);
			$phpCode = $this->compilerPlugins[$className]->executeStart($tagArgs, $this);
		}
		else {
			$phpCode = $this->compilerPlugins[$className]->executeEnd($this);
		}
		
		return $phpCode;
	}

	/**
	 * Compiles a capture tag.
	 *
	 * @param 	boolean 	$startTag
	 * @param 	string 		$captureTag
	 * @return 	string 				phpCode
	 */
	protected function compileCaptureTag($startTag, $captureTag = null) {
		if ($startTag) {
			$append = false;
			$args = $this->parseTagArgs($captureTag, 'capture');
			
			if (!isset($args['name'])) {
				$args['name'] = "'default'";
			}
			
			if (!isset($args['assign'])) {
				if (isset($args['append'])) {
					$args['assign'] = $args['append'];
					$append = true;
				}
				else {
					$args['assign'] = '';
				}
			}
			
			$this->captureStack[] = array('name' => $args['name'], 'variable' => $args['assign'], 'append' => $append);
			return '<?php ob_start(); ?>';
		}
		else {
			$capture = array_pop($this->captureStack);
			$phpCode = "<?php\n";
			$phpCode .= "\$this->v['tpl']['capture'][".$capture['name']."] = ob_get_clean();\n";
			if (!empty($capture['variable'])) $phpCode .= "\$this->".($capture['append'] ? 'append' : 'assign')."(".$capture['variable'].", \$this->v['tpl']['capture'][".$capture['name']."]);\n";
			$phpCode .= "?>";
			return $phpCode;
		}
	}
	
	/**
	 * Compiles a section tag.
	 *
	 * @param 	string 		$sectionTag
	 * @return 	string 				phpCode
	 */
	protected function compileSectionTag($sectionTag) {
		$args = $this->parseTagArgs($sectionTag, 'section');

		// check arguments
		if (!isset($args['loop'])) {
			throw new SystemException($this->formatSyntaxError("missing 'loop' attribute in section tag", $this->currentIdentifier, $this->currentLineNo));
		}
		if (!isset($args['name'])) {
			throw new SystemException($this->formatSyntaxError("missing 'name' attribute in section tag", $this->currentIdentifier, $this->currentLineNo));
		}
		if (!isset($args['show'])) {
			$args['show'] = true;
		}
		
		$sectionProp = "\$this->v['tpl']['section'][".$args['name']."]";

		$phpCode = "<?php\n";
		$phpCode .= "if (".$args['loop'].") {\n";
		$phpCode .= $sectionProp." = array();\n";
		$phpCode .= $sectionProp."['loop'] = (is_array(".$args['loop'].") ? count(".$args['loop'].") : max(0, (int)".$args['loop']."));\n";
		$phpCode .= $sectionProp."['show'] = ".$args['show'].";\n";
		if (!isset($args['step'])) {
			$phpCode .= $sectionProp."['step'] = 1;\n";
		}
		else {
			$phpCode .= $sectionProp."['step'] = ".$args['step'].";\n";
		}
		if (!isset($args['max'])) {
			$phpCode .= $sectionProp."['max'] = ".$sectionProp."['loop'];\n";
		}
		else {
			$phpCode .= $sectionProp."['max'] = (".$args['max']." < 0 ? ".$sectionProp."['loop'] : ".$args['max'].");\n";
		}
		if (!isset($args['start'])) {
			$phpCode .= $sectionProp."['start'] = (".$sectionProp."['step'] > 0 ? 0 : ".$sectionProp."['loop'] - 1);\n";
		}
		else {
			$phpCode .= $sectionProp."['start'] = ".$args['start'].";\n";
			$phpCode .= "if (".$sectionProp."['start'] < 0) {\n";
			$phpCode .= $sectionProp."['start'] = max(".$sectionProp."['step'] > 0 ? 0 : -1, ".$sectionProp."['loop'] + ".$sectionProp."['start']);\n}\n";
			$phpCode .= "else {\n";
			$phpCode .= $sectionProp."['start'] = min(".$sectionProp."['start'], ".$sectionProp."['step'] > 0 ? ".$sectionProp."['loop'] : ".$sectionProp."['loop'] - 1);\n}\n";
		}
		
		if (!isset($args['start']) && !isset($args['step']) && !isset($args['max'])) {
			$phpCode .= $sectionProp."['total'] = ".$sectionProp."['loop'];\n";
		} else {
			$phpCode .= $sectionProp."['total'] = min(ceil((".$sectionProp."['step'] > 0 ? ".$sectionProp."['loop'] - ".$sectionProp."['start'] : ".$sectionProp."['start'] + 1) / abs(".$sectionProp."['step'])), ".$sectionProp."['max']);\n";
		}
		$phpCode .= "if (".$sectionProp."['total'] == 0) ".$sectionProp."['show'] = false;\n";
		$phpCode .= "} else {\n";
		$phpCode .= "".$sectionProp."['total'] = 0;\n";
		$phpCode .= "".$sectionProp."['show'] = false;}\n";

		$phpCode .= "if (".$sectionProp."['show']) {\n";
		$phpCode .= "for (".$sectionProp."['index'] = ".$sectionProp."['start'], ".$sectionProp."['rowNumber'] = 1;\n";
		$phpCode .= $sectionProp."['rowNumber'] <= ".$sectionProp."['total'];\n";
		$phpCode .= $sectionProp."['index'] += ".$sectionProp."['step'], ".$sectionProp."['rowNumber']++) {\n";
		$phpCode .= "\$this->v[".$args['name']."] = ".$sectionProp."['index'];\n";
		$phpCode .= $sectionProp."['previousIndex'] = ".$sectionProp."['index'] - ".$sectionProp."['step'];\n";
		$phpCode .= $sectionProp."['nextIndex'] = ".$sectionProp."['index'] + ".$sectionProp."['step'];\n";
		$phpCode .= $sectionProp."['first']      = (".$sectionProp."['rowNumber'] == 1);\n";
		$phpCode .= $sectionProp."['last']       = (".$sectionProp."['rowNumber'] == ".$sectionProp."['total']);\n";
		$phpCode .= "?>";

		return $phpCode;
	}
	
	/**
	 * Compiles a foreach tag.
	 *
	 * @param 	string 		$foreachTag
	 * @return 	string 				phpCode
	 */
	protected function compileForeachTag($foreachTag) {
		$args = $this->parseTagArgs($foreachTag, 'foreach');

		// check arguments
		if (!isset($args['from'])) {
			throw new SystemException($this->formatSyntaxError("missing 'from' attribute in foreach tag", $this->currentIdentifier, $this->currentLineNo));
		}
		if (!isset($args['item'])) {
			throw new SystemException($this->formatSyntaxError("missing 'item' attribute in foreach tag", $this->currentIdentifier, $this->currentLineNo));
		}

		$foreachProp = '';
		if (isset($args['name'])) {
			$foreachProp = "\$this->v['tpl']['foreach'][".$args['name']."]";
		}
		
		$phpCode = "<?php\n";
		if (!empty($foreachProp)) {
			$phpCode .= $foreachProp."['total'] = count(".$args['from'].");\n";
			$phpCode .= $foreachProp."['show'] = (".$foreachProp."['total'] > 0 ? true : false);\n";
			$phpCode .= $foreachProp."['iteration'] = 0;\n";
		}
		$phpCode .= "if (count(".$args['from'].") > 0) {\n";
		
		if (isset($args['key'])) {
			$phpCode .= "foreach (".$args['from']." as ".(StringUtil::substring($args['key'], 0, 1) != '$' ? "\$this->v[".$args['key']."]" : $args['key'])." => ".(StringUtil::substring($args['item'], 0, 1) != '$' ? "\$this->v[".$args['item']."]" : $args['item']).") {\n";
		}
		else {
			$phpCode .= "foreach (".$args['from']." as ".(StringUtil::substring($args['item'], 0, 1) != '$' ? "\$this->v[".$args['item']."]" : $args['item']).") {\n";
		}
		
		if (!empty($foreachProp)) {
			$phpCode .= $foreachProp."['first'] = (".$foreachProp."['iteration'] == 0 ? true : false);\n";
			$phpCode .= $foreachProp."['last'] = ((".$foreachProp."['iteration'] == ".$foreachProp."['total'] - 1) ? true : false);\n";
			$phpCode .= $foreachProp."['iteration']++;\n";
		}
		
		$phpCode .= "?>";
		return $phpCode;
	}
	
	/**
	 * Compiles an include tag.
	 *
	 * @param 	string 		$includeTag
	 * @return 	string 				phpCode
	 */
	protected function compileIncludeTag($includeTag) {
		$args = $this->parseTagArgs($includeTag, 'include');
		$append = false;
		
		// check arguments
		if (!isset($args['file'])) {
			throw new SystemException($this->formatSyntaxError("missing 'file' attribute in include tag", $this->currentIdentifier, $this->currentLineNo));
		}

		// get filename
		$file = $args['file'];
		unset($args['file']);

		// special parameters
		$assignVar = false;
		if (isset($args['assign'])) {
			$assignVar = $args['assign'];
			unset($args['assign']);
		}
		
		if (isset($args['append'])) {
			$assignVar = $args['append'];
			$append = true;
			unset($args['append']);
		}
		
		$sandbox = true;
		if (isset($args['sandbox'])) {
			$sandbox = $args['sandbox'];
			unset($args['sandbox']);
		}
		
		$once = false;
		if (isset($args['once'])) {
			$once = $args['once'];
			unset($args['once']);
		}
		
		// make argument string
		$argString = $this->makeArgString($args);
		
		// build phpCode
		$phpCode = "<?php\n";
		if ($once) $phpCode .= "if (!isset(\$this->v['tpl']['includedTemplates'][".$file."])) {\n";
		$hash = StringUtil::getRandomID();
		$phpCode .= "\$outerTemplateName".$hash." = \$this->v['tpl']['template'];\n";
		
		if ($assignVar !== false) {
			$phpCode .= "ob_start();\n";
		}
		
		$phpCode .= '$this->includeTemplate('.$file.', array('.$argString.'), ('.$sandbox.' ? 1 : 0), $this->v[\'__PACKAGE_ID\']);'."\n";
		
		if ($assignVar !== false) {
			$phpCode .= '$this->'.($append ? 'append' : 'assign').'('.$assignVar.', ob_get_clean());'."\n";
		}
		
		$phpCode .= "\$this->v['tpl']['template'] = \$outerTemplateName".$hash.";\n";
		$phpCode .= "\$this->v['tpl']['includedTemplates'][".$file."] = 1;\n";
		if ($once) $phpCode .= "}\n";
		$phpCode .= '?>';
		
		return $phpCode;
	}
	
	/**
	 * Parses an argument list and returns
	 * the keys and values in an associative array.
	 *
	 * @param 	string 		$tagArgs
	 * @param 	string		$tag
	 * @return 	array 		$tagArgs
	 */
	public function parseTagArgs($tagArgs, $tag) {
		// replace strings
		$tagArgs = $this->replaceQuotes($tagArgs);
		
		// validate tag arguments
		if (!preg_match('~^(?:\s+\w+\s*=\s*[^=]*(?=\s|$))*$~s', $tagArgs)) {
			throw new SystemException($this->formatSyntaxError('syntax error in tag {'.$tag.'}', $this->currentIdentifier, $this->currentLineNo));
		}

		// parse tag arguments
		$matches = array();
		// find all variables
		preg_match_all('~\s+(\w+)\s*=\s*([^=]*)(?=\s|$)~s', $tagArgs, $matches);
		$args = array();
		for ($i = 0, $j = count($matches[1]); $i < $j; $i++) {
			$name = $matches[1][$i];
			$string = $this->compileVariableTag($matches[2][$i], false);
			
			// reinserts strings
			foreach (StringStack::getStack('singleQuote') as $hash => $value) {
				if (StringUtil::indexOf($string, $hash) !== false) {
					$string = StringUtil::replace($hash, $value, $string);
				}
			}
			foreach (StringStack::getStack('doubleQuote') as $hash => $value) {
				if (StringUtil::indexOf($string, $hash) !== false) {
					$string = StringUtil::replace($hash, $value, $string);
				}
			}
			
			$args[$name] = $string;
		}
		
		// clear stack
		$this->reinsertQuotes('');
		
		return $args;
	}
	
	/**
	 * Takes an array created by TemplateCompiler::parseTagArgs()
	 * and creates a string.
	 *
	 * @param 	array 		$args
	 * @return 	string 		$args
	 */
	public static function makeArgString($args) {
		$argString = '';
		foreach	($args as $key => $val) {
			if ($argString != '') {
				$argString .= ', ';
			}
			$argString .= "'$key' => $val";
		}
		return $argString;
	}
	
	/**
	 * Formats a syntax error message.
	 * 
	 * @param	string		$errorMsg
	 * @param	string		$file
	 * @param	integer		$line
	 * @return	string				formatted error message
	 */
	public static function formatSyntaxError($errorMsg, $file = null, $line = null) {
		$errorMsg = 'Template compilation failed: '.$errorMsg;
		if ($file && $line) {
			$errorMsg .= " in template '$file' on line $line";
		}
		elseif ($file && !$line) {
			$errorMsg .= " in template '$file'";
		}
		return $errorMsg;
	}
	
	/**
	 * Compiles an {if} Tag
	 *
	 * @param 	string 		$tagArgs
	 * @param	boolean		$elseif		true, if this tag is an else tag
	 * @return	string 				php code of this tag
	 */
	protected function compileIfTag($tagArgs, $elseif = false) {
		$tagArgs = $this->replaceQuotes($tagArgs);
		$tagArgs = str_replace(' ', '', $tagArgs);
		
		// split tags
		preg_match_all('~('.$this->conditionOperatorPattern.')~', $tagArgs, $matches);
		$operators = $matches[1];
		$values = preg_split('~(?:'.$this->conditionOperatorPattern.')~', $tagArgs);
		$leftParentheses = 0;
		$result = '';
		
		for ($i = 0, $j = count($values); $i < $j; $i++) {
			$operator = (isset($operators[$i]) ? $operators[$i] : null);
			
			if ($operator !== '!' && $values[$i] == '') {
				throw new SystemException($this->formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo));
			}
			
			$leftParenthesis = StringUtil::countSubstring($values[$i], '(');
			$rightParenthesis = StringUtil::countSubstring($values[$i], ')');
			if ($leftParenthesis > $rightParenthesis) {
				$leftParentheses += $leftParenthesis - $rightParenthesis;
				$value = StringUtil::substring($values[$i], $leftParenthesis - $rightParenthesis);
				$result .= str_repeat('(', $leftParenthesis - $rightParenthesis);
				
				if (str_replace('(', '', StringUtil::substring($values[$i], 0, $leftParenthesis - $rightParenthesis)) != '') {
					throw new SystemException($this->formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo));
				}
			}
			else if ($leftParenthesis < $rightParenthesis) {
				$leftParentheses += $leftParenthesis - $rightParenthesis;
				$value = StringUtil::substring($values[$i], 0, $leftParenthesis - $rightParenthesis);
				
				if ($leftParentheses < 0 || str_replace(')', '', StringUtil::substring($values[$i], $leftParenthesis - $rightParenthesis)) != '') {
					throw new SystemException($this->formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo));
				}
			}
			else $value = $values[$i];
			
			try {
				$result .= $this->compileVariableTag($value, false);
			}
			catch (SystemException $e) {
				throw new SystemException($this->formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo));
			}
			
			if ($leftParenthesis < $rightParenthesis) {
				$result .= str_repeat(')', $rightParenthesis - $leftParenthesis);
			}
			
			if ($operator) $result .= ' '.$operator.' ';
		}
		
		return '<?php '.($elseif ? '} elseif' : 'if').' ('.$result.') { ?>';
	}
	
	/**
	 * Adds a tag to the tag stack.
	 *
	 * @param 	string 		$tag
	 */
	public function pushTag($tag) {
		$this->tagStack[] = array($tag, $this->currentLineNo);
	}
	
	/**
	 * Deletes a tag from the tag stack.
	 *
	 * @param 	string 		$tag
	 * @return 	string 		$tag
	 */
	public function popTag($tag) {
		list($openTag, $lineNo) = array_pop($this->tagStack);
		if ($tag == $openTag) {
			return $openTag;
		}
		if ($tag == 'if' && ($openTag == 'else' || $openTag == 'elseif')) {
			return $this->popTag($tag);
		}
		if ($tag == 'foreach' && $openTag == 'foreachelse') {
			return $this->popTag($tag);
		}
		if ($tag == 'section' && $openTag == 'sectionelse') {
			return $this->popTag($tag);
		}
	}
	
	/**
	 * Compiles an output tag.
	 * 
	 * @param	string		$tag
	 * @return	string			php code of this tag
	 */
	protected function compileOutputTag($tag) {
		$encodeHTML = false;
		$formatNumeric = false;
		if ($tag[0] == '@') {
			$tag = StringUtil::substring($tag, 1);
		}
		else if ($tag[0] == '#') {
			$tag = StringUtil::substring($tag, 1);
			$formatNumeric = true;
		}
		else {
			$encodeHTML = true;
		}
		
		$parsedTag = $this->compileVariableTag($tag);
		
		// the @ operator at the beginning of an output avoids
		// the default call of StringUtil::encodeHTML()
		if ($encodeHTML) {
			$parsedTag = 'wcf\util\StringUtil::encodeHTML('.$parsedTag.')';
		}
		// the # operator at the beginning of an output instructs
		// the complier to call the StringUtil::formatNumeric() method
		else if ($formatNumeric) {
			$parsedTag = 'wcf\util\StringUtil::formatNumeric('.$parsedTag.')';
		}
		
		return '<?php echo '.$parsedTag.'; ?>';
	}
	
	/**
	 * Compiles a variable tag.
	 * 
	 * @param	string		$variable
	 * @param	string		$type
	 * @param	boolean		$allowConstants
	 * @return	string
	 */
	protected function compileSimpleVariable($variable, $type = '', $allowConstants = true) {
		if ($type == '') $type = $this->getVariableType($variable);
		
		if ($type == 'variable') return '$this->v[\''.substr($variable, 1).'\']';
		else if ($type == 'string') return $variable;
		else if ($allowConstants && ($variable == 'true' || $variable == 'false' || $variable == 'null' || preg_match('/^[A-Z0-9_]*$/', $variable))) return $variable;
		else return "'".$variable."'";
	}
	
	/**
	 * Compiles a modifier tag.
	 * 
	 * @param	array		$data
	 * @return	string
	 */
	protected function compileModifier($data) {
		if (isset($data['className'])) {
			return "\$this->pluginObjects['".$data['className']."']->execute(array(".implode(',', $data['parameter'])."), \$this)";
		}
		else {
			return $data['name'].'('.implode(',', $data['parameter']).')';
		}
	}
	
	/**
	 * Returns type of the given variable
	 * 
	 * @param	string		$variable
	 * @return	string
	 */
	protected function getVariableType($variable) {
		if (substr($variable, 0, 1) == '$') return 'variable';
		else if (substr($variable, 0, 2) == '@@') return 'string';
		else return 'constant';
	}
	
	/**
	 * Compiles a variable tag.
	 * 
	 * @param	string		$tag
	 * @return	string
	 */
	public function compileVariableTag($tag, $replaceQuotes = true) {
		// replace all quotes with unique hash values
		$compiledTag = $tag;
		if ($replaceQuotes) $compiledTag = $this->replaceQuotes($compiledTag);
		// replace numbers and special constants
		$compiledTag = $this->replaceConstants($compiledTag);
		
		// split tags
		preg_match_all('~('.$this->variableOperatorPattern.')~', $compiledTag, $matches);
		$operators = $matches[1];
		$values = preg_split('~(?:'.$this->variableOperatorPattern.')~', $compiledTag);
		
		// parse tags
		$statusStack = array(0 => 'start');
		$result = '';
		$modifierData = null;
		for ($i = 0, $j = count($values); $i < $j; $i++) {
			// check value
			$status = end($statusStack);
			$operator = (isset($operators[$i]) ? $operators[$i] : null);
			$values[$i] = trim($values[$i]);
			
			if ($values[$i] !== '') {
				$variableType = $this->getVariableType($values[$i]);
				
				switch ($status) {
					case 'start': 
						$result .= $this->compileSimpleVariable($values[$i], $variableType);
						$statusStack[0] = $status = $variableType;
						break;
						
					case 'object access':
						if (/*strpos($values[$i], '$') !== false || */strpos($values[$i], '@@') !== false) {
							throw new SystemException($this->formatSyntaxError("unexpected '->".$values[$i]."' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						}
						if (strpos($values[$i], '$') !== false) $result .= '{'.$this->compileSimpleVariable($values[$i], $variableType).'}';
						else $result .= $values[$i];
						$statusStack[count($statusStack) - 1] = $status = 'object';
						break;
					
					case 'object method start':
						$statusStack[count($statusStack) - 1] = 'object method';
						$result .= $this->compileSimpleVariable($values[$i], $variableType);
						$statusStack[] = $status = $variableType;
						break;
						
					case 'object method parameter separator':
						array_pop($statusStack);
						$result .= $this->compileSimpleVariable($values[$i], $variableType);
						$statusStack[] = $status = $variableType;
						break;

					case 'dot access':
						$result .= $this->compileSimpleVariable($values[$i], $variableType, false);
						$result .= ']';
						$statusStack[count($statusStack) - 1] = $status = 'variable';
						break;
						
					case 'object method':
					case 'left parenthesis':	
						$result .= $this->compileSimpleVariable($values[$i], $variableType);
						$statusStack[] = $status = $variableType;
						break;
						
					case 'bracket open':
						$result .= $this->compileSimpleVariable($values[$i], $variableType, false);
						$statusStack[] = $status = $variableType;
						break;

					case 'math':
						$result .= $this->compileSimpleVariable($values[$i], $variableType);
						$statusStack[count($statusStack) - 1] = $status = $variableType;
						break;
						
					case 'modifier end':
						$result .= $this->compileSimpleVariable($values[$i], $variableType);
						$statusStack[] = $status = $variableType;
						break;
					
					case 'modifier':
						if (strpos($values[$i], '$') !== false || strpos($values[$i], '@@') !== false) {
							throw new SystemException($this->formatSyntaxError("unknown modifier '".$values[$i]."'", $this->currentIdentifier, $this->currentLineNo));
						}
						
						// handle modifier name
						$modifierData['name'] = $values[$i];
						$className = $this->template->getPluginClassName('modifier', $modifierData['name']);
						if (class_exists($className)) {
							$modifierData['className'] = $className;
							$this->autoloadPlugins[$modifierData['className']] = $modifierData['className'];	
						}
						else if ((!function_exists($modifierData['name']) && !in_array($modifierData['name'], $this->unknownPHPFunctions)) || in_array($modifierData['name'], $this->disabledPHPFunctions)) {
							throw new SystemException($this->formatSyntaxError("unknown modifier '".$values[$i]."'", $this->currentIdentifier, $this->currentLineNo));
						}
						
						$statusStack[count($statusStack) - 1] = $status = 'modifier end';
						break;
						
					case 'object':
					case 'constant': 
					case 'variable':
					case 'string': 
						throw new SystemException($this->formatSyntaxError('unknown tag {'.$tag.'}', $this->currentIdentifier, $this->currentLineNo));
						break;
				}
			}

			// check operator
			if ($operator !== null) {
				switch ($operator) {
					case '.': 
						if ($status == 'variable' || $status == 'object') {
							if ($status == 'object') $statusStack[count($statusStack) - 1] = 'variable';
							$result .= '[';
							$statusStack[] = 'dot access';
							break;
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected '.' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
					
					// object access
					case '->':
						if ($status == 'variable' || $status == 'object') {
							$result .= $operator;
							$statusStack[count($statusStack) - 1] = 'object access';
							break;
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected '->' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
					
					// left parenthesis
					case '(':
						if ($status == 'object') {
							$statusStack[count($statusStack) - 1] = 'variable';
							$statusStack[] = 'object method start';
							$result .= $operator;
							break;
						}
						else if ($status == 'math' || $status == 'start' || $status == 'left parenthesis' || $status == 'bracket open' || $status == 'modifier end') {
							if ($status == 'start') $statusStack[count($statusStack) - 1] = 'constant';
							$statusStack[] = 'left parenthesis';
							$result .= $operator;
							break;
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected '(' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
					
					// right parenthesis
					case ')': 
						while ($oldStatus = array_pop($statusStack)) {
							if ($oldStatus != 'variable' && $oldStatus != 'object' && $oldStatus != 'constant' && $oldStatus != 'string') {
								if ($oldStatus == 'object method start' || $oldStatus == 'object method' || $oldStatus == 'left parenthesis') {
									$result .= $operator;
									break 2;
								}
								else break;
							}
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected ')' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
					
					// bracket open
					case '[': 
						if ($status == 'variable' || $status == 'object') {
							if ($status == 'object') $statusStack[count($statusStack) - 1] = 'variable';
							$statusStack[] = 'bracket open';
							$result .= $operator;
							break;
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected '[' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
					
					// bracket close
					case ']':
						while ($oldStatus = array_pop($statusStack)) {
							if ($oldStatus != 'variable' && $oldStatus != 'object' && $oldStatus != 'constant' && $oldStatus != 'string') {
								if ($oldStatus == 'bracket open') {
									$result .= $operator;
									break 2;
								}
								else break;
							}
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected ']' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;

					// modifier
					case '|': 
						// handle previous modifier
						if ($modifierData !== null) {
							if ($result !== '') $modifierData['parameter'][] = $result;
							$result = $this->compileModifier($modifierData);
						}
						
						// clear status stack
						while ($oldStatus = array_pop($statusStack)) {
							if ($oldStatus != 'variable' && $oldStatus != 'object' && $oldStatus != 'constant' && $oldStatus != 'string' && $oldStatus != 'modifier end') {
								throw new SystemException($this->formatSyntaxError("unexpected '|' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
							}
						}
						
						$statusStack = array(0 => 'modifier');
						$modifierData = array('name' => '', 'parameter' => array(0 => $result));
						$result = '';
						break;

					// modifier parameter
					case ':': 
						while ($oldStatus = array_pop($statusStack)) {
							if ($oldStatus != 'variable' && $oldStatus != 'object' && $oldStatus != 'constant' && $oldStatus != 'string') {
								if ($oldStatus == 'modifier end') {
									$statusStack[] = 'modifier end';
									if ($result !== '') $modifierData['parameter'][] = $result;
									$result = '';
									break 2;
								}
								else break;
							}
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected ':' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
						
					case ',':
						while ($oldStatus = array_pop($statusStack)) {
							if ($oldStatus != 'variable' && $oldStatus != 'object' && $oldStatus != 'constant' && $oldStatus != 'string') {
								if ($oldStatus == 'object method') {
									$result .= $operator;
									$statusStack[] = 'object method';
									$statusStack[] = 'object method parameter separator';
									break 2;
								}
								else break;
							}
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected ',' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
					
					// math operators	
					case '+':
					case '-':
					case '*':
					case '/':
					case '%':
					case '^':
						if ($status == 'variable' || $status == 'object' || $status == 'constant' ||  $status == 'string' || $status == 'modifier end') {
							$result .= $operator;
							$statusStack[count($statusStack) - 1] = 'math';
							break;
						}
						
						throw new SystemException($this->formatSyntaxError("unexpected '".$operator."' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
				}
			}
		}
		
		// handle open modifier
		if ($modifierData !== null) {
			if ($result !== '') $modifierData['parameter'][] = $result;
			$result = $this->compileModifier($modifierData);
		}
		
		// reinserts strings
		$result = $this->reinsertQuotes($result);
		$result = $this->reinsertConstants($result);
		
		return $result;
	}
	
	/**
	 * Generates the regexp pattern.
	 */
	protected function buildPattern() {
		$this->variableOperatorPattern = '\-\>|\.|\(|\)|\[|\]|\||\:|\+|\-|\*|\/|\%|\^|\,';
		$this->conditionOperatorPattern = '===|!==|==|!=|<=|<|>=|(?<!-)>|\|\||&&|!|=';
		$this->escapedPattern = '(?<!\\\\)';
		$this->validVarnamePattern = '(?:[a-zA-Z_][a-zA-Z_0-9]*)';
		$this->constantPattern = '(?:[A-Z_][A-Z_0-9]*)';
		//$this->doubleQuotePattern = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"';
		$this->doubleQuotePattern = '"(?:[^"\\\\]+|\\\\.)*"';
		//$this->singleQuotePattern = '\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\'';
		$this->singleQuotePattern = '\'(?:[^\'\\\\]+|\\\\.)*\'';
		$this->quotePattern = '(?:' . $this->doubleQuotePattern . '|' . $this->singleQuotePattern . ')';
		$this->numericPattern = '(?i)(?:(?:\-?\d+(?:\.\d+)?)|true|false|null)';
		$this->simpleVarPattern = '(?:\$('.$this->validVarnamePattern.'))';
		$this->outputPattern = '(?:(?:@|#)?(?:'.$this->constantPattern.'|'.$this->quotePattern.'|'.$this->numericPattern.'|'.$this->simpleVarPattern.'|\())';
	}
	
	/**
	 * Returns the instance of the template engine class.
	 * 
	 * @return	wcf\system\templateTemplateEngine
	 */
	public function getTemplate() {
		return $this->template;
	}
	
	/**
	 * Returns the left delimiter for template tags.
	 * 
	 * @return	string
	 */
	public function getLeftDelimiter() {
		return $this->leftDelimiter;
	}
	
	/**
	 * Returns the right delimiter for template tags.
	 * 
	 * @return	string
	 */
	public function getRightDelimiter() {
		return $this->rightDelimiter;
	}
	
	/**
	 * Returns the name of the current template.
	 * 
	 * @return	string
	 */
	public function getCurrentIdentifier() {
		return $this->currentIdentifier;
	}
	
	/**
	 * Returns the current line number.
	 * 
	 * @return	integer
	 */
	public function getCurrentLineNo() {
		return $this->currentLineNo;
	}
	
	/**
	 * Applies the prefilters to the given string.
	 * 
	 * @param	string		$templateName
	 * @param	string		$string
	 * @return	string
	 */
	public function applyPrefilters($templateName, $string) {
		foreach ($this->template->getPrefilters() as $prefilter) {
			if (!is_object($prefilter)) {
				$className = $this->template->getPluginClassName('prefilter', $prefilter);
				if (!class_exists($className)) {
					throw new SystemException($this->formatSyntaxError('unable to find prefilter class '.$className, $this->currentIdentifier));
				}
				$prefilter = new $className();
			}
			
			if ($prefilter instanceof IPrefilterTemplatePlugin) {
				$string = $prefilter->execute($templateName, $string, $this);
			}
			else {
				throw new SystemException($this->formatSyntaxError("Prefilter '".(is_object($prefilter) ? get_class($prefilter) : $prefilter)."' does not implement the interface 'IPrefilterTemplatePlugin'", $this->currentIdentifier));
			}
		}
		
		return $string;
	}
	
	/**
	 * Replaces all {literal} Tags with unique hash values.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function replaceLiterals($string) {
		return preg_replace_callback("~".$this->ldq."literal".$this->rdq."(.*?)".$this->ldq."/literal".$this->rdq."~s", array($this, 'replaceLiteralsCallback'), $string);
	}
	
	/**
	 * Reinserts the literal tags.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function reinsertLiterals($string) {
		return StringStack::reinsertStrings($string, 'literal');
	}
	
	/**
	 * Callback function used in replaceLiterals()
	 */
	private function replaceLiteralsCallback($matches) {
		return StringStack::pushToStringStack($matches[1], 'literal');
	}
	
	/**
	 * Removes template comments
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function removeComments($string) {
		return preg_replace("~".$this->ldq."\*.*?\*".$this->rdq."~s", '', $string);
	}
	
	/**
	 * Replaces all quotes with unique hash values.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function replaceQuotes($string) {
		$string = preg_replace_callback('~\'([^\'\\\\]+|\\\\.)*\'~', array($this, 'replaceSingleQuotesCallback'), $string);
		$string = preg_replace_callback('~"([^"\\\\]+|\\\\.)*"~', array($this, 'replaceDoubleQuotesCallback'), $string);
		
		return $string;
	}
	
	/**
	 * Callback function used in replaceQuotes()
	 */
	private function replaceSingleQuotesCallback($matches) {
		return StringStack::pushToStringStack($matches[0], 'singleQuote');
	}
	
	/**
	 * Callback function used in replaceQuotes()
	 */
	private function replaceDoubleQuotesCallback($matches) {
		// parse unescaped simple vars in double quotes
		// replace $foo with {$this->v['foo']}
		$matches[0] = preg_replace('~'.$this->escapedPattern.$this->simpleVarPattern.'~', '{$this->v[\'\\1\']}', $matches[0]);
		return StringStack::pushToStringStack($matches[0], 'doubleQuote');
	}
	
	/**
	 * Reinserts the quotes.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function reinsertQuotes($string) {
		$string = StringStack::reinsertStrings($string, 'singleQuote');
		$string = StringStack::reinsertStrings($string, 'doubleQuote');
		
		return $string;
	}
	
	/**
	 * Replaces all constants with unique hash values.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function replaceConstants($string) {
		return preg_replace_callback('~(?<=^|'.$this->variableOperatorPattern.')(?i)((?:\-?\d+(?:\.\d+)?)|true|false|null)(?=$|'.$this->variableOperatorPattern.')~', array($this, 'replaceConstantsCallback'), $string);
	}
	
	/**
	 * Callback function used in replaceConstants()
	 */
	private function replaceConstantsCallback($matches) {
		return StringStack::pushToStringStack($matches[1], 'constants');
	}
	
	/**
	 * Reinserts the constants.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function reinsertConstants($string) {
		return StringStack::reinsertStrings($string, 'constants');
	}
	
	/**
	 * Replaces all php tags.
	 * 
	 * @param	string		$string
	 * @return	string
	 */
	public function replacePHPTags($string) {
		if (StringUtil::indexOf($string, '<?') !== false) {
			$string = StringUtil::replace('<?php', '@@PHP_START_TAG@@', $string);
			$string = StringUtil::replace('<?', '@@PHP_SHORT_START_TAG@@', $string);
			$string = StringUtil::replace('?>', '@@PHP_END_TAG@@', $string);
			$string = StringUtil::replace('@@PHP_END_TAG@@', "<?php echo '?>'; ?>\n", $string);
			$string = StringUtil::replace('@@PHP_SHORT_START_TAG@@', "<?php echo '<?'; ?>\n", $string);
			$string = StringUtil::replace('@@PHP_START_TAG@@', "<?php echo '<?php'; ?>\n", $string);
		}
		
		return $string;
	}
}
