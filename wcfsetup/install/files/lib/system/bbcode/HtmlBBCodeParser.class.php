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
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 * @since	3.0
 */
class HtmlBBCodeParser extends BBCodeParser {
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
		
		// difference to the original implementation: we don't care for unclosed tags,
		// they'll be marked as invalid and removed at the end, leaving lonely opening
		// tags that will eventually be removed within the marker processor
		$this->buildXMLStructure();
		
		$this->handleSourceBBCodes();
		
		$this->buildParsedString();
		
		return $this->parsedText;
	}
	
	/**
	 * @inheritDoc
	 */
	public function buildXMLStructure() {
		// stack for open tags
		$openTagStack = $openTagDataStack = [];
		$newTagArray = [];
		$newTextArray = [];
		
		$i = -1;
		foreach ($this->tagArray as $i => $tag) {
			if ($tag['closing']) {
				// closing tag
				if (in_array($tag['name'], $openTagStack) && $this->isAllowed($openTagStack, $tag['name'], true)) {
					// close unclosed tags
					while (($previousTag = end($openTagStack)) != $tag['name']) {
						$nextIndex = count($newTagArray);
						
						// mark as invalid and do not flag as opened tag
						$newTag = $this->buildTag('[/'.end($openTagStack).']');
						$newTag['invalid'] = true;
						
						$newTagArray[$nextIndex] = $newTag;
						if (!isset($newTextArray[$nextIndex])) $newTextArray[$nextIndex] = '';
						$newTextArray[$nextIndex] .= $this->textArray[$i];
						$this->textArray[$i] = '';
						array_pop($openTagStack);
						array_pop($openTagDataStack);
					}
					
					$nextIndex = count($newTagArray);
					$newTagArray[$nextIndex] = $tag;
					array_pop($openTagStack);
					array_pop($openTagDataStack);
					if (!isset($newTextArray[$nextIndex])) $newTextArray[$nextIndex] = '';
					$newTextArray[$nextIndex] .= $this->textArray[$i];
				}
				else {
					// no such tag open
					// handle as plain text
					$this->textArray[$i] .= $tag['source'];
					$last = count($newTagArray);
					if (!isset($newTextArray[$last])) $newTextArray[$last] = '';
					$newTextArray[$last] .= $this->textArray[$i];
				}
			}
			else {
				// opening tag
				if ($this->isAllowed($openTagStack, $tag['name']) && $this->isValidTag($tag)) {
					$openTagStack[] = $tag['name'];
					$openTagDataStack[] = $tag;
					$nextIndex = count($newTagArray);
					$newTagArray[$nextIndex] = $tag;
					if (!isset($newTextArray[$nextIndex])) $newTextArray[$nextIndex] = '';
					$newTextArray[$nextIndex] .= $this->textArray[$i];
				}
				else {
					// tag not allowed
					$this->textArray[$i] .= $tag['source'];
					$last = count($newTagArray);
					if (!isset($newTextArray[$last])) $newTextArray[$last] = '';
					$newTextArray[$last] .= $this->textArray[$i];
				}
			}
		}
		
		$last = count($newTagArray);
		if (!isset($newTextArray[$last])) $newTextArray[$last] = '';
		$newTextArray[$last] .= $this->textArray[$i + 1];
		
		// close unclosed open tags
		while (end($openTagStack)) {
			$nextIndex = count($newTagArray);
			
			// mark as invalid
			$newTag = $this->buildTag('[/'.end($openTagStack).']');
			$newTag['invalid'] = true;
			
			$newTagArray[$nextIndex] = $newTag;
			if (!isset($newTextArray[$nextIndex])) $newTextArray[$nextIndex] = '';
			array_pop($openTagStack);
			array_pop($openTagDataStack);
		}
		
		$this->tagArray = $newTagArray;
		$this->textArray = $newTextArray;
	}
	
	/**
	 * Flags bbcodes inside code bbcodes for reversal, turning them back
	 * into their source state (= textual representation).
	 */
	protected function handleSourceBBCodes() {
		$sourceBBCodes = $this->getSourceBBCodes();
		
		$inCode = '';
		$openTagStack = [];
		
		for ($i = 0, $length = count($this->tagArray); $i < $length; $i++) {
			$tag = $this->tagArray[$i];
			
			if (!empty($tag['invalid'])) {
				continue;
			}
			
			$name = $tag['name'];
			
			if ($tag['closing']) {
				if ($inCode) {
					// matches opening code tag
					if ($inCode === $name) {
						$inCode = '';
						array_pop($openTagStack);
					}
					else {
						// unrelated tag, flag as invalid
						$this->tagArray[$i]['inCode'] = true;
					}
					
					continue;
				}
				
				array_pop($openTagStack);
			}
			else {
				if ($inCode) {
					// inside code block, flag as invalid
					$this->tagArray[$i]['inCode'] = true;
					continue;
				}
				
				// starts a new code block
				if (in_array($name, $sourceBBCodes)) {
					// look ahead to see if there is a valid closing tag
					$hasClosingTag = false;
					for ($j = $i + 1; $j < $length; $j++) {
						if ($this->tagArray[$j]['name'] === $name && empty($this->tagArray[$j]['invalid'])) {
							$hasClosingTag = true;
							break;
						}
					}
					
					if ($hasClosingTag) {
						$inCode = $name;
					}
					else {
						// no closing tag, flag as invalid to avoid the
						// entire content afterwards being treated as code
						$this->tagArray[$i]['inCode'] = true;
					}
				}
				
				$openTagStack[] = $name;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function buildParsedString() {
		// reset parsed text
		$this->parsedText = '';
		
		// reset identifiers for open tags
		$this->openTagIdentifiers = [];
		
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
				if (!empty($tag['invalid'])) {
					// drop invalid closing tag
					continue;
				}
				else if (!empty($tag['inCode'])) {
					// revert bbcodes inside code
					$buffer .= $tag['source'];
					continue;
				}
				
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
						// build tag
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
				if (!empty($tag['inCode'])) {
					// revert bbcodes inside code
					$buffer .= $tag['source'];
					continue;
				}
				
				// opening tag
				if ($this->needBuffering($tag)) {
					// start buffering
					$tag['buffer'] = '';
					$bufferedTagStack[] = $tag;
					$buffer =& $bufferedTagStack[(count($bufferedTagStack) - 1)]['buffer'];
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
	 * @return	string		parsed bbcode
	 */
	public function getHtmlOutput($name, array $attributes) {
		if (isset($this->bbcodes[$name])) {
			$openingTag = ['attributes' => $attributes, 'name' => $name];
			$closingTag = ['name' => $name];
			
			if ($this->bbcodes[$name]->getProcessor()) {
				/** @var IBBCode $processor */
				$processor = $this->bbcodes[$name]->getProcessor();
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
		
		$uuid = StringUtil::getUUID();
		$this->openTagIdentifiers[] = [
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
		if (!$this->isValidBBCodeName($name)) {
			return $tag['source'];
		}
		
		$data = array_pop($this->openTagIdentifiers);
		if ($data['name'] !== $name) {
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
