<?php
namespace wcf\system\bbcode;
use wcf\system\exception\SystemException;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Parses bbcodes and transforms them into the custom HTML element <woltlab-bbcode>
 * that can be safely passed through HTMLPurifier's validation mechanism.
 * 
 * All though not exactly required for all bbcodes, the actual output of an bbcode
 * cannot be foreseen and potentially conflict with HTMLPurifier's whitelist. Examples
 * are <iframe> or other embedded media that is allowed as a result of a bbcode, but
 * not allowed to be directly provided by a user. 
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 * @since	3.0
 */
class HtmlBBCodeParser extends BBCodeParser {
	/**
	 * list of bbcodes that cannot be nested
	 * @var string[]
	 */
	public static $disallowNesting = ['attach', 'b', 'code', 'email', 'i', 'img', 'media', 's', 'tt', 'u', 'url', 'user', 'wsm', 'wsp'];
	
	/**
	 * list of open tags with name and uuid
	 * @var array
	 */
	protected $openTagIdentifiers = [];
	
	/**
	 * regex for valid bbcode names
	 * @var string
	 */
	protected $validBBCodePattern = '~^[a-z](?:[a-z0-9\-_]+)?$~';
	
	/**
	 * @inheritDoc
	 */
	public function parse($text) {
		$this->setText($text);
		
		// difference to the original implementation: sourcecode bbcodes are handled too
		$this->buildTagArray(false);
		
		$this->ignoreUnclosedTags();
		
		$this->buildXMLStructure();
		
		$this->buildParsedString();
		
		return $this->parsedText;
	}
	
	/**
	 * Reverts tags to their source representation if they either
	 * have no matching counter part (such as opening tags without
	 * closing one), or if they're inside code bbcodes.
	 */
	protected function ignoreUnclosedTags() {
		$length = count($this->tagArray);
		
		// step 1) validate source bbcodes
		$inSource = null;
		$sourceBBCodes = $this->getSourceBBCodes();
		foreach ($this->tagArray as $i => &$tag) {
			$name = $tag['name'];
			$tag['valid'] = true;
			
			if ($tag['closing']) {
				if ($inSource === null) {
					continue;
				}
				
				if ($name === $inSource['name']) {
					$inSource = null;
				}
				else {
					$tag['valid'] = false;
				}
			}
			else {
				if ($inSource !== null) {
					$tag['valid'] = false;
					continue;
				}
				
				if (in_array($name, $sourceBBCodes)) {
					// look ahead to see if there is a closing tag
					$hasClosingTag = false;
					for ($j = $i + 1; $j < $length; $j++) {
						if ($this->tagArray[$j]['closing'] && $this->tagArray[$j]['name'] === $name) {
							$hasClosingTag = true;
							break;
						}
					}
					
					if ($hasClosingTag) {
						$inSource = $tag;
					}
					else {
						$tag['valid'] = false;
					}
				}
			}
		}
		unset($tag);
		
		// step 2) check if tags are properly opened and closed, incorrect nesting doesn't matter here
		foreach ($this->tagArray as $i => &$tag) {
			if (!$tag['valid']) {
				continue;
			}
			
			if ($tag['closing']) {
				if (!isset($tag['matching'])) {
					$tag['valid'] = false;
				}
			}
			else {
				$name = $tag['name'];
				
				// find matching closing tag
				$hasClosingTag = false;
				$badTags = [];
				for ($j = $i + 1; $j < $length; $j++) {
					$sibling = $this->tagArray[$j];
					if ($sibling['name'] === $name) {
						if (!$sibling['closing']) {
							if (!in_array($name, self::$disallowNesting)) {
								continue;
							}
							
							// disallow the same tag opened again
							$badTags[] = $j;
						}
						else if (!isset($sibling['matching'])) {
							$this->tagArray[$j]['matching'] = true;
							$hasClosingTag = true;
							break;
						}
					}
				}
				
				if ($hasClosingTag) {
					foreach ($badTags as $j) {
						$this->tagArray[$j]['valid'] = false;
					}
				}
				else {
					$tag['valid'] = false;
				}
			}
		}
		unset($tag);
		
		// rebuild tag array
		$newTagArray = $newTextArray = [];
		$buffer = '';
		foreach ($this->tagArray as $i => $tag) {
			if ($tag['valid']) {
				// cleanup
				unset($tag['matching']);
				unset($tag['valid']);
				
				$newTagArray[] = $tag;
				$newTextArray[] = $buffer . $this->textArray[$i];
				$buffer = '';
			}
			else {
				$buffer .= $this->textArray[$i] . $tag['source'];
			}
		}
		
		// text array always holds one more item for the content after the last tag
		$newTextArray[] = $buffer . $this->textArray[count($this->textArray) - 1];
		
		$this->tagArray = $newTagArray;
		$this->textArray = $newTextArray;
	}
	
	/**
	 * Builds the parsed string.
	 */
	public function buildParsedString() {
		// reset parsed text
		$this->parsedText = '';
		
		// create text buffer
		$buffer =& $this->parsedText;
		
		// stack of buffered tags
		$bufferedTagStack = [];
		
		// loop through the tags
		$i = -1;
		foreach ($this->tagArray as $i => $tag) {
			// append text to buffer
			$buffer .= $this->textArray[$i];
			
			if ($tag['closing']) {
				// get buffered opening tag
				$openingTag = end($bufferedTagStack);
				
				// closing tag
				if ($openingTag && $openingTag['name'] == $tag['name']) {
					$hideBuffer = false;
					// insert buffered content as attribute value
					foreach ($this->bbcodes[$tag['name']]->getAttributes() as $attribute) {
						if ($attribute->useText && !isset($openingTag['attributes'][$attribute->attributeNo])) {
							$openingTag['attributes'][$attribute->attributeNo] = $buffer;
							$hideBuffer = true;
							break;
						}
					}
					
					// validate tag attributes again
					if ($this->isValidTag($openingTag)) {
						if ($this->bbcodes[$tag['name']]->className) {
							// difference to the original implementation: use the custom HTML element than to process them directly
							$parsedTag = $this->compileTag($openingTag, $buffer, $tag);
						}
						else {
							// build tag
							$parsedTag = $this->buildOpeningTag($openingTag);
							$closingTag = $this->buildClosingTag($tag);
							if (!empty($closingTag) && $hideBuffer) $parsedTag .= $buffer.$closingTag;
						}
					}
					else {
						$parsedTag = $openingTag['source'].$buffer.$tag['source'];
					}
					
					// close current buffer
					array_pop($bufferedTagStack);
					
					// open previous buffer
					if (count($bufferedTagStack) > 0) {
						$bufferedTag =& $bufferedTagStack[count($bufferedTagStack) - 1];
						$buffer =& $bufferedTag['buffer'];
					}
					else {
						$buffer =& $this->parsedText;
					}
					
					// append parsed tag
					$buffer .= $parsedTag;
				}
				else {
					$buffer .= $this->buildClosingTag($tag);
				}
			}
			else {
				// opening tag
				if ($this->needBuffering($tag)) {
					// start buffering
					$tag['buffer'] = '';
					$bufferedTagStack[] = $tag;
					$buffer =& $bufferedTagStack[count($bufferedTagStack) - 1]['buffer'];
				}
				else {
					$buffer .= $this->buildOpeningTag($tag);
				}
			}
		}
		
		if (isset($this->textArray[$i + 1])) $this->parsedText .= $this->textArray[$i + 1];
	}
	
	/**
	 * Builds the bbcode output.
	 * 
	 * @param	string		$name		bbcode identifier
	 * @param	array		$attributes	list of attributes
	 * @param       \DOMElement     $element        element
	 * @return	string		parsed bbcode
	 */
	public function getHtmlOutput($name, array $attributes, \DOMElement $element) {
		if (isset($this->bbcodes[$name])) {
			$bbcode = $this->bbcodes[$name];
			if ($bbcode->isSourceCode) {
				array_unshift($attributes, $element->textContent);
			}
			
			$openingTag = ['attributes' => $attributes, 'name' => $name];
			$closingTag = ['name' => $name];
			
			if ($bbcode->getProcessor()) {
				/** @var IBBCode $processor */
				$processor = $bbcode->getProcessor();
				return $processor->getParsedTag($openingTag, '<!-- META_CODE_INNER_CONTENT -->', $closingTag, $this);
			}
			else {
				return parent::buildOpeningTag($openingTag) . '<!-- META_CODE_INNER_CONTENT -->' . parent::buildClosingTag($closingTag);
			}
		}
		
		// unknown bbcode, output plain tags
		return $this->buildBBCodeTag($name, $attributes);
	}
	
	/**
	 * Builds a plain bbcode string, used for unknown bbcodes.
	 * 
	 * @param	string		$name			bbcode identifier
	 * @param	array		$attributes		list of attributes
	 * @param	boolean		$openingTagOnly		only render the opening tag
	 * @return	string
	 */
	public function buildBBCodeTag($name, $attributes, $openingTagOnly = false) {
		if (!empty($attributes)) {
			foreach ($attributes as &$attribute) {
				$attribute = "'" . addcslashes($attribute, "'") . "'";
			}
			unset($attribute);
			
			$attributes = '=' . implode(",", $attributes);
		}
		else {
			$attributes = '';
		}
		
		if ($openingTagOnly) {
			return '[' . $name . $attributes . ']';
		}
		
		return '[' . $name . $attributes . ']<!-- META_CODE_INNER_CONTENT -->[/' . $name . ']';
	}
	
	/**
	 * Returns the list of bbcodes that represent block elements.
	 * 
	 * @return	string[]	list of bbcode block elements
	 */
	public function getBlockBBCodes() {
		$bbcodes = [];
		foreach ($this->bbcodes as $name => $bbcode) {
			if ($bbcode->isBlockElement) {
				$bbcodes[] = $name;
			}
		}
		
		return $bbcodes;
	}
	
	/**
	 * Returns the list of bbcodes that represent source code elements.
	 * 
	 * @return	string[]	list of bbcode source code elements
	 */
	public function getSourceBBCodes() {
		$bbcodes = [];
		foreach ($this->bbcodes as $name => $bbcode) {
			if ($bbcode->isSourceCode) {
				$bbcodes[] = $name;
			}
		}
		
		return $bbcodes;
	}
	
	/**
	 * Compiles tag fragments into the custom HTML element.
	 * 
	 * @param	array   $openingTag	opening tag data
	 * @param	string  $content	content between opening and closing tag
	 * @param	array   $closingTag	closing tag data
	 * @return	string  custom HTML element
	 */
	protected function compileTag(array $openingTag, $content, array $closingTag) {
		return $this->buildOpeningTag($openingTag) . $content . $this->buildClosingTag($closingTag);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function buildOpeningTag(array $tag) {
		$name = strtolower($tag['name']);
		if (!$this->isValidBBCodeName($name)) {
			return $tag['source'];
		}
		
		$index = (isset($tag['bufferPlaceholder'])) ? $index = $tag['bufferPlaceholder'] : count($this->openTagIdentifiers);
		
		$uuid = StringUtil::getUUID();
		$this->openTagIdentifiers[$index] = [
			'name' => $name,
			'uuid' => $uuid
		];
		
		$attributes = '';
		if (!empty($tag['attributes'])) {
			// strip outer quote tags
			$tag['attributes'] = array_map(function($attribute) {
				if (preg_match('~^([\'"])(?P<content>.*)(\1)$~', $attribute, $matches)) {
					return $matches['content'];
				}
				
				return $attribute;
			}, $tag['attributes']);
			
			// uses base64 encoding to avoid an "escape" nightmare
			$attributes = ' data-attributes="' . base64_encode(JSON::encode($tag['attributes'])) . '"';
		}
		
		return '<woltlab-metacode-marker data-name="' . $name . '" data-uuid="' . $uuid . '" data-source="' . base64_encode($tag['source']) . '"' . $attributes . ' />';
	}
	
	/**
	 * @inheritDoc
	 */
	protected function buildClosingTag(array $tag) {
		$name = strtolower($tag['name']);
		if (!$this->isValidBBCodeName($name) || empty($this->openTagIdentifiers)) {
			return $tag['source'];
		}
		
		$data = array_pop($this->openTagIdentifiers);
		if ($data['name'] !== $name) {
			// check if this is a source code tag as some people
			// love to nest the same source bbcode
			if (in_array($name, $this->getSourceBBCodes())) {
				return $tag['source'];
			}
			
			throw new SystemException("Tag mismatch, expected '".$name."', got '".$data['name']."'.");
		}
		
		return '<woltlab-metacode-marker data-uuid="' . $data['uuid'] . '" data-source="' . base64_encode($tag['source']) . '" />';
	}
	
	/**
	 * Returns true if provided name is a valid bbcode identifier.
	 * 
	 * @param	string		$name		bbcode identifier
	 * @return	boolean		true if provided name is a valid bbcode identifier
	 */
	protected function isValidBBCodeName($name) {
		return preg_match($this->validBBCodePattern, $name) === 1;
	}
}
