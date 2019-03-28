<?php
namespace wcf\system\html\node;

use wcf\data\bbcode\BBCode;
use wcf\data\ITitledObject;
use wcf\system\Regex;
use wcf\util\DOMUtil;
use wcf\util\JSON;

class HtmlNodePlainLink {
	protected $href = '';
	
	/**
	 * @var \DOMElement
	 */
	protected $link;
	protected $objectID = 0;
	protected $pristine = true;
	protected $standalone = false;
	
	/**
	 * @var \DOMElement
	 */
	protected $topLevelParent;
	
	public function __construct(\DOMElement $link, $href) {
		$this->link = $link;
		$this->href = $href;
	}
	
	public function setIsInline() {
		$this->standalone = false;
		$this->topLevelParent = null;
		
		return $this;
	}
	
	public function setIsStandalone(\DOMElement $topLevelParent) {
		$this->standalone = true;
		$this->topLevelParent = $topLevelParent;
		
		return $this;
	}
	
	public function isPristine() {
		return $this->pristine;
	}
	
	public function isStandalone() {
		return $this->standalone;
	}
	
	public function detectObjectID(Regex $regex) {
		if ($regex->match($this->href, true)) {
			$this->objectID = $regex->getMatches()[2][0];
		}
		
		return $this->objectID;
	}
	
	public function getObjectID() {
		return $this->objectID;
	}
	
	public function setTitle(ITitledObject $object) {
		$this->markAsTainted();
		
		$this->link->nodeValue = '';
		$this->link->appendChild($this->link->ownerDocument->createTextNode($object->getTitle()));
	}
	
	public function replaceWithBBCode(BBCode $bbcode) {
		$this->markAsTainted();
		
		if ($this->objectID === 0) {
			throw new \UnexpectedValueException('The objectID must not be null.');
		}
		
		$metacodeElement = $this->link->ownerDocument->createElement('woltlab-metacode');
		$metacodeElement->setAttribute('data-name', $bbcode->bbcodeTag);
		$metacodeElement->setAttribute('data-attributes', base64_encode(JSON::encode([$this->objectID])));
		
		if ($bbcode->isBlockElement) {
			if (!$this->isStandalone()) {
				throw new \LogicException('Cannot inject a block bbcode in an inline context.');
			}
			
			// Replace the top level parent with the link itself, which will be replaced with the bbcode afterwards.
			$this->topLevelParent->insertBefore($this->link, $this->topLevelParent);
			DOMUtil::removeNode($this->topLevelParent);
		}
		
		DOMUtil::replaceElement($this->link, $metacodeElement, false);
	}
	
	protected function markAsTainted() {
		if (!$this->pristine) {
			throw new \RuntimeException('This link has already been modified.');
		}
		
		$this->pristine = false;
	}
}
