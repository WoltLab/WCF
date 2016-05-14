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
 * cannot be forseen and potentially conflict with HTMLPurifier's whitelist. Examples
 * are <iframe> or other embedded media that is allowed as a result of a bbcode, but
 * not allowed to be directly provided by a user. 
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
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
		
		$this->buildXMLStructure();
		$this->buildParsedString();
		
		return $this->parsedText;
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
		$bufferedTagStack = array();
		
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
	 * Compiles tag fragments into the custom HTML element.
	 * 
	 * @param       array   $openingTag     opening tag data
	 * @param       string  $content        content between opening and closing tag
	 * @param       array   $closingTag     closing tag data
	 * @return      string  custom HTML element
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
			// uses base64 encoding to avoid an "escape" nightmare
			$attributes = ' data-attributes="' . base64_encode(JSON::encode($tag['attributes'])) . '"';
		}
		
		return '<woltlab-metacode-marker data-name="' . $name . '" data-uuid="' . $uuid . '"' . $attributes . ' />';
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
		
		return '<woltlab-metacode-marker data-uuid="' . $data['uuid'] . '" />';
	}
	
	/**
	 * Returns true if provided name is a valid bbcode identifier.
	 * 
	 * @param       string          $name           bbcode identifier
	 * @return      boolean         true if provided name is a valid bbcode identifier
	 */
	protected function isValidBBCodeName($name) {
		return preg_match($this->validBBCodePattern, $name) === 1;
	}
}
