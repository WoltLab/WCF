<?php
namespace wcf\system\template;
use wcf\system\exception\SystemException;
use wcf\system\template\plugin\ICompilerTemplatePlugin;
use wcf\system\template\plugin\IPrefilterTemplatePlugin;
use wcf\util\StringStack;
use wcf\util\StringUtil;

/**
 * Compiles template sources into valid PHP code.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Template
 */
class TemplateScriptingCompiler {
	/**
	 * template engine object
	 * @var	TemplateEngine
	 */
	protected $template;
	
	/**
	 * PHP functions that can be used in the modifier syntax and are unknown
	 * to PHP's function_exists function
	 * @var	string[]
	 */
	protected $unknownPHPFunctions = ['isset', 'unset', 'empty'];
	
	/**
	 * PHP functions that can not be used in the modifier syntax
	 * @var	string[]
	 */
	protected $disabledPHPFunctions = [
		'system', 'exec', 'passthru', 'shell_exec', // command line execution
		'include', 'require', 'include_once', 'require_once', // includes
		'eval', 'virtual', 'call_user_func_array', 'call_user_func', 'assert' // code execution
	];
	
	/**
	 * PHP functions and modifiers that can be used in enterprise mode
	 * @var	string[]
	 */
	protected $enterpriseFunctions = [
		'addslashes',
		'array_keys',
		'array_pop',
		'array_slice',
		'array_values',
		'base64_decode',
		'base64_encode',
		'ceil',
		'concat',
		'constant',
		'count',
		'currency',
		'current',
		'date',
		'defined',
		'doubleval',
		'empty',
		'end',
		'explode',
		'file_exists',
		'filesize',
		'floatval',
		'floor',
		'function_exists',
		'gmdate',
		'hash',
		'htmlspecialchars',
		'html_entity_decode',
		'implode',
		'in_array',
		'is_array',
		'is_numeric',
		'is_object',
		'intval',
		'is_subclass_of',
		'isset',
		'json_encode',
		'key',
		'lcfirst',
		'ltrim',
		'max',
		'mb_strpos',
		'mb_strlen',
		'mb_strpos',
		'mb_strtolower',
		'mb_strtoupper',
		'mb_substr',
		'md5',
		'method_exists',
		'microtime',
		'min',
		'nl2br',
		'number_format',
		'parse_url',
		'preg_match',
		'preg_replace',
		'print_r',
		'rawurlencode',
		'reset',
		'round',
		'sha1',
		'spl_object_hash',
		'sprintf',
		'strip_tags',
		'strlen',
		'strpos',
		'strtolower',
		'strtotime',
		'strtoupper',
		'str_pad',
		'str_repeat',
		'str_replace',
		'str_ireplace',
		'substr',
		'trim',
		'ucfirst',
		'uniqid',
		'urlencode',
		'wcfDebug',
		'wordwrap'
	];
	
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
	
	/**
	 * list of automatically loaded tenplate plugins
	 * @var	string[]
	 */
	protected $autoloadPlugins = [];
	
	/**
	 * stack with template tags data
	 * @var	array
	 */
	protected $tagStack = [];
	
	/**
	 * list of loaded compiler plugin objects
	 * @var	ICompilerTemplatePlugin[]
	 */
	protected $compilerPlugins = [];
	
	/**
	 * stack used to compile the capture tag
	 * @var	array
	 */
	protected $captureStack = [];
	
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
	 * list of static includes per template
	 * @var	string[][]
	 */
	protected $staticIncludes = [];
	
	/**
	 * data of all currently active `foreach` loops; entry data:
	 * 
	 * 	hash => unique foreach loop hash
	 * 	itemVar => template code for the `item` variable
	 * 	keyVar => (optional) template code for the `key` variable
	 * 
	 * @var	string[][]
	 */
	protected $foreachLoops = [];
	
	/**
	 * Creates a new TemplateScriptingCompiler object.
	 * 
	 * @param	TemplateEngine		$template
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
	 * @param	array		$metaData
	 * @param	boolean		$isolated
	 * @return	array|boolean
	 * @throws	SystemException
	 */
	public function compileString($identifier, $sourceContent, array $metaData = [], $isolated = false) {
		$previousData = [];
		if ($isolated) {
			$previousData = [
				'autoloadPlugins' => $this->autoloadPlugins,
				'currentIdentifier' => $this->currentIdentifier,
				'currentLineNo' => $this->currentLineNo,
				'tagStack' => $this->tagStack
			];
		}
		else {
			$this->staticIncludes = [];
		}
		
		// reset vars
		$this->autoloadPlugins = $this->tagStack = [];
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
		$matches = [];
		preg_match_all("~".$this->ldq."(.*?)".$this->rdq."~s", $sourceContent, $matches);
		$templateTags = $matches[1];
		
		// Split content by template tags to obtain non-template content
		$textBlocks = preg_split("~".$this->ldq.".*?".$this->rdq."~s", $sourceContent);
		
		// compile the template tags into php-code
		$compiledTags = [];
		for ($i = 0, $j = count($templateTags); $i < $j; $i++) {
			$this->currentLineNo += mb_substr_count($textBlocks[$i], "\n");
			
			if ($templateTags[$i] === '') {
				// avoid empty JavaScript object literals being recognized
				// as template scripting tags
				$compiledTags[] = '{}';
			}
			else {
				$compiledTags[] = $this->compileTag($templateTags[$i], $identifier, $metaData);
			}
			
			$this->currentLineNo += mb_substr_count($templateTags[$i], "\n");
		}
		
		// throw error messages for unclosed tags
		if (count($this->tagStack) > 0) {
			foreach ($this->tagStack as $tagStack) {
				throw new SystemException(static::formatSyntaxError('unclosed tag {'.$tagStack[0].'}', $this->currentIdentifier, $tagStack[1]));
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
		$compiledContent = rtrim($compiledContent);
		
		// reinsert {literal} Tags
		$compiledContent = $this->reinsertLiterals($compiledContent);
		
		// include Plugins
		$compiledAutoloadPlugins = '';
		if (count($this->autoloadPlugins) > 0) {
			$compiledAutoloadPlugins = "<?php\n";
			foreach ($this->autoloadPlugins as $className) {
				$compiledAutoloadPlugins .= "if (!isset(\$this->pluginObjects['$className'])) {\n";
				$compiledAutoloadPlugins .= "\$this->pluginObjects['$className'] = new $className;\n";
				$compiledAutoloadPlugins .= "}\n";
			}
			$compiledAutoloadPlugins .= "?>";
		}
		
		// restore data
		if ($isolated) {
			$this->autoloadPlugins = $previousData['autoloadPlugins'];
			$this->currentIdentifier = $previousData['currentIdentifier'];
			$this->currentLineNo = $previousData['currentLineNo'];
			$this->tagStack = $previousData['tagStack'];
		}
		
		return [
			'meta' => [
				'include' => $this->staticIncludes
			],
			'template' => $compiledAutoloadPlugins.$compiledContent
		];
	}
	
	/**
	 * Compiles a template tag.
	 * 
	 * @param	string		$tag
	 * @param	string		$identifier
	 * @param	array		$metaData
	 * @return	string
	 * @throws	SystemException
	 */
	protected function compileTag($tag, $identifier, array &$metaData) {
		if (preg_match('~^'.$this->outputPattern.'~s', $tag)) {
			// variable output
			return $this->compileOutputTag($tag);
		}
		
		$match = [];
		// replace 'else if' with 'elseif'
		$tag = preg_replace('~^else\s+if(?=\s)~i', 'elseif', $tag);
		
		if (preg_match('~^(/?\w+)~', $tag, $match)) {
			// build in function or plugin
			$tagCommand = $match[1];
			$tagArgs = mb_substr($tag, mb_strlen($tagCommand));
			
			switch ($tagCommand) {
				case 'if':
					$this->pushTag('if');
					return $this->compileIfTag($tagArgs);
					
				case 'elseif':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'if' && $openTag != 'elseif') {
						throw new SystemException(static::formatSyntaxError('unxepected {elseif}', $this->currentIdentifier, $this->currentLineNo));
					}
					else if ($openTag == 'if') {
						$this->pushTag('elseif');
					}
					return $this->compileIfTag($tagArgs, true);
					
				case 'else':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'if' && $openTag != 'elseif') {
						throw new SystemException(static::formatSyntaxError('unexpected {else}', $this->currentIdentifier, $this->currentLineNo));
					}
					$this->pushTag('else');
					return '<?php } else { ?>';
					
				case '/if':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'if' && $openTag != 'elseif' && $openTag != 'else') {
						throw new SystemException(static::formatSyntaxError('unexpected {/if}', $this->currentIdentifier, $this->currentLineNo));
					}
					$this->popTag('if');
					return '<?php } ?>';
					
				case 'include':
					return $this->compileIncludeTag($tagArgs, $identifier, $metaData);
					
				case 'foreach':
					$this->pushTag('foreach');
					return $this->compileForeachTag($tagArgs);
					
				case 'foreachelse':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'foreach') {
						throw new SystemException(static::formatSyntaxError('unexpected {foreachelse}', $this->currentIdentifier, $this->currentLineNo));
					}
					$this->pushTag('foreachelse');
					return '<?php } } else { { ?>';
					
				case '/foreach':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'foreach' && $openTag != 'foreachelse') {
						throw new SystemException(static::formatSyntaxError('unexpected {/foreach}', $this->currentIdentifier, $this->currentLineNo));
					}
					$this->popTag('foreach');
					return $this->compileForeachEndTag();
					
				case 'section':
					$this->pushTag('section');
					return $this->compileSectionTag($tagArgs);
					
				case 'sectionelse':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'section') {
						throw new SystemException(static::formatSyntaxError('unexpected {sectionelse}', $this->currentIdentifier, $this->currentLineNo));
					}
					$this->pushTag('sectionelse');
					return '<?php } } else { { ?>';
					
				case '/section':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'section' && $openTag != 'sectionelse') {
						throw new SystemException(static::formatSyntaxError('unexpected {/section}', $this->currentIdentifier, $this->currentLineNo));
					}
					$this->popTag('section');
					return "<?php } } ?>";
					
				case 'capture':
					$this->pushTag('capture');
					return $this->compileCaptureTag(true, $tagArgs);
					
				case '/capture':
					list($openTag) = end($this->tagStack);
					if ($openTag != 'capture') {
						throw new SystemException(static::formatSyntaxError('unexpected {/capture}', $this->currentIdentifier, $this->currentLineNo));
					}
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
		
		throw new SystemException(static::formatSyntaxError('unknown tag {'.$tag.'}', $this->currentIdentifier, $this->currentLineNo));
	}
	
	/**
	 * Compiles a function plugin and returns the output of the plugin or false
	 * if the plugin doesn't exist.
	 * 
	 * @param	string		$tagCommand
	 * @param	string		$tagArgs
	 * @return	mixed
	 */
	protected function compileFunctionPlugin($tagCommand, $tagArgs) {
		$className = $this->template->getPluginClassName('function', $tagCommand);
		if (!class_exists($className)) {
			return false;
		}
		$this->autoloadPlugins[$className] = $className;
		
		$tagArgs = static::makeArgString($this->parseTagArgs($tagArgs, $tagCommand));
		
		return "<?=\$this->pluginObjects['".$className."']->execute([".$tagArgs."], \$this);?>";
	}
	
	/**
	 * Compiles a block plugin and returns the output of the plugin or false
	 * if the plugin doesn't exist.
	 * 
	 * @param	string		$tagCommand
	 * @param	string		$tagArgs
	 * @return	mixed
	 * @throws	SystemException
	 */
	protected function compileBlockPlugin($tagCommand, $tagArgs) {
		// check whether this is the start ({block}) or the
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
			
			$tagArgs = static::makeArgString($this->parseTagArgs($tagArgs, $tagCommand));
			
			$phpCode = "<?php \$this->tagStack[] = ['".$tagCommand."', [".$tagArgs."]];\n";
			$phpCode .= "\$this->pluginObjects['".$className."']->init(\$this->tagStack[count(\$this->tagStack) - 1][1], \$this);\n";
			$phpCode .= "while (\$this->pluginObjects['".$className."']->next(\$this)) { ob_start(); ?>";
		}
		else {
			list($openTag) = end($this->tagStack);
			if ($openTag != $tagCommand) {
				throw new SystemException(static::formatSyntaxError('unexpected {/'.$tagCommand.'}', $this->currentIdentifier, $this->currentLineNo));
			}
			$this->popTag($tagCommand);
			$phpCode = "<?php echo \$this->pluginObjects['".$className."']->execute(\$this->tagStack[count(\$this->tagStack) - 1][1], ob_get_clean(), \$this); }\n";
			$phpCode .= "array_pop(\$this->tagStack);?>";
		}
		
		return $phpCode;
	}
	
	/**
	 * Compiles a compiler function/block and returns the output of the plugin
	 * or false if the plugin doesn't exist.
	 * 
	 * @param	string		$tagCommand
	 * @param	string		$tagArgs
	 * @return	mixed
	 * @throws	SystemException
	 */
	protected function compileCompilerPlugin($tagCommand, $tagArgs) {
		// check whether this is the start ({block}) or the
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
				throw new SystemException(static::formatSyntaxError("Compiler plugin '".$tagCommand."' does not implement the interface 'ICompilerTemplatePlugin'", $this->currentIdentifier));
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
	 * Compiles a capture tag and returns the compiled PHP code.
	 * 
	 * @param	boolean		$startTag
	 * @param	string		$captureTag
	 * @return	string
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
			
			$this->captureStack[] = ['name' => $args['name'], 'variable' => $args['assign'], 'append' => $append];
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
	 * Compiles a section tag and returns the compiled PHP code.
	 * 
	 * @param	string		$sectionTag
	 * @return	string
	 * @throws	SystemException
	 */
	protected function compileSectionTag($sectionTag) {
		$args = $this->parseTagArgs($sectionTag, 'section');
		
		// check arguments
		if (!isset($args['loop'])) {
			throw new SystemException(static::formatSyntaxError("missing 'loop' attribute in section tag", $this->currentIdentifier, $this->currentLineNo));
		}
		if (!isset($args['name'])) {
			throw new SystemException(static::formatSyntaxError("missing 'name' attribute in section tag", $this->currentIdentifier, $this->currentLineNo));
		}
		if (!isset($args['show'])) {
			$args['show'] = true;
		}
		
		$sectionProp = "\$this->v['tpl']['section'][".$args['name']."]";
		
		$phpCode = "<?php\n";
		$phpCode .= "if (".$args['loop'].") {\n";
		$phpCode .= $sectionProp." = [];\n";
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
		$phpCode .= $sectionProp."['first'] = (".$sectionProp."['rowNumber'] == 1);\n";
		$phpCode .= $sectionProp."['last'] = (".$sectionProp."['rowNumber'] == ".$sectionProp."['total']);\n";
		$phpCode .= "?>";
		
		return $phpCode;
	}
	
	/**
	 * Compiles a foreach tag and returns the compiled PHP code.
	 * 
	 * @param	string		$foreachTag
	 * @return	string
	 * @throws	SystemException
	 */
	protected function compileForeachTag($foreachTag) {
		$args = $this->parseTagArgs($foreachTag, 'foreach');
		
		// check arguments
		if (!isset($args['from'])) {
			throw new SystemException(static::formatSyntaxError("missing 'from' attribute in foreach tag", $this->currentIdentifier, $this->currentLineNo));
		}
		if (!isset($args['item'])) {
			throw new SystemException(static::formatSyntaxError("missing 'item' attribute in foreach tag", $this->currentIdentifier, $this->currentLineNo));
		}
		
		$foreachProp = '';
		if (isset($args['name'])) {
			$foreachProp = "\$this->v['tpl']['foreach'][".$args['name']."]";
		}
		
		$hash = StringUtil::getRandomID();
		$foreachHash = "\$_foreach_" . $hash;
		$foreachData = ['hash' => $hash];
		
		$phpCode = "<?php\n";
		$phpCode .= $foreachHash." = ".$args['from'].";\n";
		
		if (empty($foreachProp)) {
			$phpCode .= "if ((is_countable(".$foreachHash.") && count(".$foreachHash.") > 0) || (!is_countable(".$foreachHash.") && ".$foreachHash.")) {\n";
		}
		else {
			$phpCode .= $foreachHash."_cnt = (".$foreachHash." !== null ? 1 : 0);\n";
			$phpCode .= "if (is_countable(".$foreachHash.")) {\n";
			$phpCode .= $foreachHash."_cnt = count(".$foreachHash.");\n";
			$phpCode .= "}\n";
			$phpCode .= $foreachProp."['total'] = ".$foreachHash."_cnt;\n";
			$phpCode .= $foreachProp."['show'] = (".$foreachProp."['total'] > 0 ? true : false);\n";
			$phpCode .= $foreachProp."['iteration'] = 0;\n";
			$phpCode .= "if (".$foreachHash."_cnt > 0) {\n";
		}
		
		$itemVar = mb_substr($args['item'], 0, 1) != '$' ? "\$this->v[".$args['item']."]" : $args['item'];
		$foreachData['itemVar'] = $itemVar;
		
		$phpCode .= "\$this->foreachVars['{$hash}'] = [];\n";
		$phpCode .= "if (isset({$itemVar})) {\n";
		$phpCode .= "\$this->foreachVars['{$hash}']['item'] = {$itemVar};\n";
		$phpCode .= "}\n";
		
		if (isset($args['key'])) {
			$keyVar = mb_substr($args['key'], 0, 1) != '$' ? "\$this->v[".$args['key']."]" : $args['key'];
			$foreachData['keyVar'] = $keyVar;
			
			$phpCode .= "if (isset({$keyVar})) {\n";
			$phpCode .= "\$this->foreachVars['{$hash}']['key'] = {$keyVar};\n";
			$phpCode .= "}\n";
			
			$phpCode .= "foreach (".$foreachHash." as {$keyVar} => {$itemVar}) {\n";
		}
		else {
			$phpCode .= "foreach (".$foreachHash." as {$itemVar}) {\n";
		}
		
		if (!empty($foreachProp)) {
			$phpCode .= $foreachProp."['first'] = (".$foreachProp."['iteration'] == 0 ? true : false);\n";
			$phpCode .= $foreachProp."['last'] = ((".$foreachProp."['iteration'] == ".$foreachProp."['total'] - 1) ? true : false);\n";
			$phpCode .= $foreachProp."['iteration']++;\n";
		}
		
		$phpCode .= "?>";
		
		$this->foreachLoops[] = $foreachData;
		
		return $phpCode;
	}
	
	/**
	 * Compiles a `/foreach` tag and returns the compiled PHP code.
	 * 
	 * @return	string
	 * @since	5.2
	 */
	protected function compileForeachEndTag() {
		$foreachData = array_pop($this->foreachLoops);
		
		// unset `item` and `key` variables and restore their sandboxed values
		$phpCode = "<?php }\n";
		$phpCode .= "unset({$foreachData['itemVar']});";
		$phpCode .= "if (isset(\$this->foreachVars['{$foreachData['hash']}']['item'])) {\n";
		$phpCode .= "{$foreachData['itemVar']} = \$this->foreachVars['{$foreachData['hash']}']['item'];\n";
		$phpCode .= "}\n";
		
		if (isset($foreachData['keyVar'])) {
			$phpCode .= "unset({$foreachData['keyVar']});";
			$phpCode .= "if (isset(\$this->foreachVars['{$foreachData['hash']}']['key'])) {\n";
			$phpCode .= "{$foreachData['keyVar']} = \$this->foreachVars['{$foreachData['hash']}']['key'];\n";
			$phpCode .= "}\n";
		}
		
		$phpCode .= "unset(\$this->foreachVars['{$foreachData['hash']}']);\n";
		$phpCode .= " } ?>";
		
		return $phpCode;
	}
	
	/**
	 * Compiles an include tag and returns the compiled PHP code.
	 * 
	 * @param	string		$includeTag
	 * @param	string		$identifier
	 * @param	array		$metaData
	 * @return	string
	 * @throws	SystemException
	 */
	protected function compileIncludeTag($includeTag, $identifier, array $metaData) {
		$args = $this->parseTagArgs($includeTag, 'include');
		$append = false;
		
		// check arguments
		if (!isset($args['file'])) {
			throw new SystemException(static::formatSyntaxError("missing 'file' attribute in include tag", $this->currentIdentifier, $this->currentLineNo));
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
		
		$once = false;
		if (isset($args['once'])) {
			$once = $args['once'];
			unset($args['once']);
		}
		
		$application = "'wcf'";
		if (isset($args['application'])) {
			$application = $args['application'];
			unset($args['application']);
		}
		
		if (preg_match('~^(\'|\")(.*)\1$~', $application, $matches)) {
			$application = $matches[2];
		}
		
		$sandbox = false;
		if (isset($args['sandbox'])) {
			$sandbox = $args['sandbox'];
			unset($args['sandbox']);
		}
		
		$sandbox = ($sandbox === 'true' || $sandbox === true || $sandbox == 1);
		
		$staticInclude = true;
		if ($sandbox || $assignVar !== false || $once !== false || strpos($application, '$') !== false || strpos($file, '$') !== false) {
			$staticInclude = false;
		}
		
		$templateName = substr($file, 1, -1);
		
		// check for static includes
		if ($staticInclude) {
			$phpCode = '';
			if (!isset($this->staticIncludes[$application])) {
				$this->staticIncludes[$application] = [];
			}
			
			if (!in_array($templateName, $this->staticIncludes[$application])) {
				$this->staticIncludes[$application][] = $templateName;
			}
			
			// pass remaining tag args as variables
			if (!empty($args)) {
				foreach ($args as $variable => $value) {
					if (substr($value, 0, 1) == "'") {
						// string values
						$phpCode .= "\$this->v['".$variable."'] = ".$value.";\n";
					}
					else {
						if (preg_match('~^\$this->v\[\'(.*)\'\]$~U', $value, $matches)) {
							// value is a variable itself
							$phpCode .= "\$this->v['".$variable."'] = ".$value.";\n";
						}
						else {
							// value is boolean, an integer or anything else
							$phpCode .= "\$this->v['".$variable."'] = ".$value.";\n";
						}
					}
				}
			}
			if (!empty($phpCode)) $phpCode = "<?php\n".$phpCode."\n?>";
			
			$sourceFilename = $this->template->getSourceFilename($templateName, $application);
			
			$data = $this->compileString($templateName, file_get_contents($sourceFilename), [
				'application' => $application,
				'data' => null,
				'filename' => ''
			], true);
			
			return $phpCode . $data['template'];
		}
		
		// make argument string
		$argString = static::makeArgString($args);
		
		// build phpCode
		$phpCode = "<?php\n";
		if ($once) $phpCode .= "if (!isset(\$this->v['tpl']['includedTemplates'][".$file."])) {\n";
		$hash = StringUtil::getRandomID();
		$phpCode .= "\$outerTemplateName".$hash." = \$this->v['tpl']['template'];\n";
		
		if ($assignVar !== false) {
			$phpCode .= "ob_start();\n";
		}
		
		if (strpos($application, '$') === false) {
			$application = "'" . $application . "'";
		}
		$phpCode .= '$this->includeTemplate('.$file.', '.$application.', ['.$argString.'], '.($sandbox ? 1 : 0).');'."\n";
		
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
	 * Parses an argument list and returns the keys and values in an associative
	 * array.
	 * 
	 * @param	string		$tagArgs
	 * @param	string		$tag
	 * @return	array
	 * @throws	SystemException
	 */
	public function parseTagArgs($tagArgs, $tag) {
		// replace strings
		$tagArgs = $this->replaceQuotes($tagArgs);
		
		// validate tag arguments
		if (!preg_match('~^(?:\s+\w+\s*=\s*[^=]*(?=\s|$))*$~s', $tagArgs)) {
			throw new SystemException(static::formatSyntaxError('syntax error in tag {'.$tag.'}', $this->currentIdentifier, $this->currentLineNo));
		}
		
		// parse tag arguments
		$matches = [];
		// find all variables
		preg_match_all('~\s+(\w+)\s*=\s*([^=]*)(?=\s|$)~s', $tagArgs, $matches);
		$args = [];
		for ($i = 0, $j = count($matches[1]); $i < $j; $i++) {
			$name = $matches[1][$i];
			$string = $this->compileVariableTag($matches[2][$i], false);
			
			// reinserts strings
			foreach (StringStack::getStack('singleQuote') as $hash => $value) {
				if (mb_strpos($string, $hash) !== false) {
					$string = str_replace($hash, $value, $string);
				}
			}
			foreach (StringStack::getStack('doubleQuote') as $hash => $value) {
				if (mb_strpos($string, $hash) !== false) {
					$string = str_replace($hash, $value, $string);
				}
			}
			
			$args[$name] = $string;
		}
		
		// clear stack
		$this->reinsertQuotes('');
		
		return $args;
	}
	
	/**
	 * Takes an array created by TemplateCompiler::parseTagArgs() and creates
	 * a string.
	 * 
	 * @param	array		$args
	 * @return	string		$args
	 */
	public static function makeArgString($args) {
		$argString = '';
		foreach ($args as $key => $val) {
			if ($argString != '') {
				$argString .= ', ';
			}
			$argString .= "'$key' => $val";
		}
		return $argString;
	}
	
	/**
	 * Returns a formatted syntax error message.
	 * 
	 * @param	string		$errorMsg
	 * @param	string		$file
	 * @param	integer		$line
	 * @return	string
	 */
	public static function formatSyntaxError($errorMsg, $file = null, $line = null) {
		$errorMsg = 'Template compilation failed: '.$errorMsg;
		if ($file && $line) {
			$errorMsg .= " in template '$file' on line $line";
		}
		else if ($file && !$line) {
			$errorMsg .= " in template '$file'";
		}
		return $errorMsg;
	}
	
	/**
	 * Compiles an {if} tag and returns the compiled PHP code.
	 * 
	 * @param	string		$tagArgs
	 * @param	boolean		$elseif		true, if this tag is an else tag
	 * @return	string
	 * @throws	SystemException
	 */
	protected function compileIfTag($tagArgs, $elseif = false) {
		$tagArgs = $this->replaceQuotes($tagArgs);
		$tagArgs = str_replace([' ', "\n"], '', $tagArgs);
		
		// split tags
		preg_match_all('~('.$this->conditionOperatorPattern.')~', $tagArgs, $matches);
		$operators = $matches[1];
		$values = preg_split('~(?:'.$this->conditionOperatorPattern.')~', $tagArgs);
		$leftParentheses = 0;
		$result = '';
		
		for ($i = 0, $j = count($values); $i < $j; $i++) {
			$operator = (isset($operators[$i]) ? $operators[$i] : null);
			
			if ($operator !== '!' && $values[$i] == '') {
				throw new SystemException(static::formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo));
			}
			
			$leftParenthesis = mb_substr_count($values[$i], '(');
			$rightParenthesis = mb_substr_count($values[$i], ')');
			if ($leftParenthesis > $rightParenthesis) {
				$leftParentheses += $leftParenthesis - $rightParenthesis;
				$value = mb_substr($values[$i], $leftParenthesis - $rightParenthesis);
				$result .= str_repeat('(', $leftParenthesis - $rightParenthesis);
				
				if (str_replace('(', '', mb_substr($values[$i], 0, $leftParenthesis - $rightParenthesis)) != '') {
					throw new SystemException(static::formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo));
				}
			}
			else if ($leftParenthesis < $rightParenthesis) {
				$leftParentheses += $leftParenthesis - $rightParenthesis;
				$value = mb_substr($values[$i], 0, $leftParenthesis - $rightParenthesis);
				
				if ($leftParentheses < 0 || str_replace(')', '', mb_substr($values[$i], $leftParenthesis - $rightParenthesis)) != '') {
					throw new SystemException(static::formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo));
				}
			}
			else $value = $values[$i];
			
			try {
				$result .= $this->compileVariableTag($value, false);
			}
			catch (SystemException $e) {
				throw new SystemException(static::formatSyntaxError('syntax error in tag {'.($elseif ? 'elseif' : 'if').'}', $this->currentIdentifier, $this->currentLineNo), 0, nl2br($e, false));
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
	 * @param	string		$tag
	 */
	public function pushTag($tag) {
		$this->tagStack[] = [$tag, $this->currentLineNo];
	}
	
	/**
	 * Deletes a tag from the tag stack.
	 * 
	 * @param	string		$tag
	 * @return	string		$tag
	 */
	public function popTag($tag) {
		list($openTag, ) = array_pop($this->tagStack);
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
	 * Compiles an output tag and returns the compiled PHP code.
	 * 
	 * @param	string		$tag
	 * @return	string
	 * @throws	SystemException
	 */
	protected function compileOutputTag($tag) {
		$encodeHTML = false;
		$formatNumeric = false;
		if ($tag[0] == '@') {
			$tag = mb_substr($tag, 1);
		}
		else if ($tag[0] == '#') {
			$tag = mb_substr($tag, 1);
			$formatNumeric = true;
		}
		else {
			$encodeHTML = true;
		}
		
		// check for forbidden constants
		if (preg_match('~^(RELATIVE_)?([A-Z]+)_DIR$~', $tag, $matches)) {
			$application = mb_strtolower($matches[2]);
			if ($application == 'wcf') {
				$application = '';
			}
			else {
				$application = "'{$application}'";
			}
			
			throw new SystemException("Accessing internal constant '".$tag."' is disallowed, please use '\$__wcf->getPath(".$application.")' instead");
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
		
		return '<?='.$parsedTag.';?>';
	}
	
	/**
	 * Compiles a variable tag and returns the compiled PHP code.
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
	 * Compiles a modifier tag and returns the compiled PHP code.
	 * 
	 * @param	array		$data
	 * @return	string
	 */
	protected function compileModifier($data) {
		if (isset($data['className'])) {
			return "\$this->pluginObjects['".$data['className']."']->execute([".implode(',', $data['parameter'])."], \$this)";
		}
		else {
			return $data['name'].'('.implode(',', $data['parameter']).')';
		}
	}
	
	/**
	 * Returns type of the given variable.
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
	 * Compiles a variable tag and returns the compiled PHP code.
	 * 
	 * @param	string		$tag
	 * @param	boolean		$replaceQuotes
	 * @return	string
	 * @throws	SystemException
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
		$statusStack = [0 => 'start'];
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
							throw new SystemException(static::formatSyntaxError("unexpected '->".$values[$i]."' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						}
						if (strpos($values[$i], '$') !== false) {
							$result .= '{'.$this->compileSimpleVariable($values[$i], $variableType).'}';
						}
						else {
							$result .= $values[$i];
						}
						
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
							throw new SystemException(static::formatSyntaxError("unknown modifier '".$values[$i]."'", $this->currentIdentifier, $this->currentLineNo));
						}
						
						// handle modifier name
						$modifierData['name'] = $values[$i];
						$className = $this->template->getPluginClassName('modifier', $modifierData['name']);
						if (class_exists($className)) {
							$modifierData['className'] = $className;
							$this->autoloadPlugins[$modifierData['className']] = $modifierData['className'];
						}
						else if (!function_exists($modifierData['name']) && !in_array($modifierData['name'], $this->unknownPHPFunctions)) {
							throw new SystemException(static::formatSyntaxError(
								"unknown modifier '".$values[$i]."'",
								$this->currentIdentifier,
								$this->currentLineNo
							));
						}
						else if (
							in_array($modifierData['name'], $this->disabledPHPFunctions)
							|| (ENABLE_ENTERPRISE_MODE && !in_array($modifierData['name'], $this->enterpriseFunctions))
						) {
							throw new SystemException(static::formatSyntaxError(
								"disabled function '".$values[$i]."'",
								$this->currentIdentifier,
								$this->currentLineNo
							));
						}
						
						$statusStack[count($statusStack) - 1] = $status = 'modifier end';
					break;
					
					case 'object':
					case 'constant': 
					case 'variable':
					case 'string': 
						throw new SystemException(static::formatSyntaxError('unknown tag {'.$tag.'}', $this->currentIdentifier, $this->currentLineNo));
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
						
						throw new SystemException(static::formatSyntaxError("unexpected '.' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
					break;
					
					// object access
					case '->':
						if ($status == 'variable' || $status == 'object') {
							$result .= $operator;
							$statusStack[count($statusStack) - 1] = 'object access';
							break;
						}
						
						throw new SystemException(static::formatSyntaxError("unexpected '->' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
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
						
						throw new SystemException(static::formatSyntaxError("unexpected '(' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
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
						
						throw new SystemException(static::formatSyntaxError("unexpected ')' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
					break;
					
					// bracket open
					case '[': 
						if ($status == 'variable' || $status == 'object') {
							if ($status == 'object') $statusStack[count($statusStack) - 1] = 'variable';
							$statusStack[] = 'bracket open';
							$result .= $operator;
							break;
						}
						
						throw new SystemException(static::formatSyntaxError("unexpected '[' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
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
						
						throw new SystemException(static::formatSyntaxError("unexpected ']' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
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
								throw new SystemException(static::formatSyntaxError("unexpected '|' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
							}
						}
						
						$statusStack = [0 => 'modifier'];
						$modifierData = ['name' => '', 'parameter' => [0 => $result]];
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
						
						throw new SystemException(static::formatSyntaxError("unexpected ':' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
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
						
						throw new SystemException(static::formatSyntaxError("unexpected ',' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
						break;
					
					// math operators
					case '+':
					case '-':
					case '*':
					case '/':
					case '%':
					case '^':
						if ($status == 'variable' || $status == 'object' || $status == 'constant' || $status == 'string' || $status == 'modifier end') {
							$result .= $operator;
							$statusStack[count($statusStack) - 1] = 'math';
							break;
						}
						
						throw new SystemException(static::formatSyntaxError("unexpected '".$operator."' in tag '".$tag."'", $this->currentIdentifier, $this->currentLineNo));
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
		$this->doubleQuotePattern = '"(?:[^"\\\\]+|\\\\.)*"';
		$this->singleQuotePattern = '\'(?:[^\'\\\\]+|\\\\.)*\'';
		$this->quotePattern = '(?:' . $this->doubleQuotePattern . '|' . $this->singleQuotePattern . ')';
		$this->numericPattern = '(?i)(?:(?:\-?\d+(?:\.\d+)?)|true|false|null)';
		$this->simpleVarPattern = '(?:\$('.$this->validVarnamePattern.'))';
		$this->outputPattern = '(?:(?:@|#)?(?:'.$this->constantPattern.'|'.$this->quotePattern.'|'.$this->numericPattern.'|'.$this->simpleVarPattern.'|\())';
	}
	
	/**
	 * Returns the instance of the template engine class.
	 * 
	 * @return	TemplateEngine
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
	 * @throws	SystemException
	 */
	public function applyPrefilters($templateName, $string) {
		foreach ($this->template->getPrefilters() as $prefilter) {
			if (!is_object($prefilter)) {
				$className = $this->template->getPluginClassName('prefilter', $prefilter);
				if (!class_exists($className)) {
					throw new SystemException(static::formatSyntaxError('unable to find prefilter class '.$className, $this->currentIdentifier));
				}
				$prefilter = new $className();
			}
			
			if ($prefilter instanceof IPrefilterTemplatePlugin) {
				$string = $prefilter->execute($templateName, $string, $this);
			}
			else {
				throw new SystemException(static::formatSyntaxError("Prefilter '".(is_object($prefilter) ? get_class($prefilter) : $prefilter)."' does not implement the interface 'IPrefilterTemplatePlugin'", $this->currentIdentifier));
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
		return preg_replace_callback("~".$this->ldq."literal".$this->rdq."(.*?)".$this->ldq."/literal".$this->rdq."~s", [$this, 'replaceLiteralsCallback'], $string);
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
	 * 
	 * @param	string[]	$matches
	 * @return	string
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
		$string = preg_replace_callback('~\'([^\'\\\\]+|\\\\.)*\'~', [$this, 'replaceSingleQuotesCallback'], $string);
		$string = preg_replace_callback('~"([^"\\\\]+|\\\\.)*"~', [$this, 'replaceDoubleQuotesCallback'], $string);
		
		return $string;
	}
	
	/**
	 * Callback function used in replaceQuotes()
	 *
	 * @param	string[]	$matches
	 * @return	string
	 */
	private function replaceSingleQuotesCallback($matches) {
		return StringStack::pushToStringStack($matches[0], 'singleQuote');
	}
	
	/**
	 * Callback function used in replaceQuotes()
	 *
	 * @param	string[]	$matches
	 * @return	string
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
		return preg_replace_callback('~(?<=^|'.$this->variableOperatorPattern.')(?i)((?:\-?\d+(?:\.\d+)?)|true|false|null)(?=$|'.$this->variableOperatorPattern.')~', [$this, 'replaceConstantsCallback'], $string);
	}
	
	/**
	 * Callback function used in replaceConstants()
	 *
	 * @param	string[]	$matches
	 * @return	string
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
		if (mb_strpos($string, '<?') !== false) {
			$string = str_replace('<?php', '@@PHP_START_TAG@@', $string);
			$string = str_replace('<?', '@@PHP_SHORT_START_TAG@@', $string);
			$string = str_replace('?>', '@@PHP_END_TAG@@', $string);
			$string = str_replace('@@PHP_END_TAG@@', "<?='?>';?>\n", $string);
			$string = str_replace('@@PHP_SHORT_START_TAG@@', "<?='<?';?>\n", $string);
			$string = str_replace('@@PHP_START_TAG@@', "<?='<?php';?>\n", $string);
		}
		
		return $string;
	}
}
