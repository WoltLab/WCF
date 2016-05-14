<?php
namespace wcf\system\html\input\filter;

/**
 * TOOD documentation
 * @since	2.2
 */
class MessageHtmlInputFilter implements IHtmlInputFilter {
	/**
	 * @var	\HTMLPurifier
	 */
	protected static $purifier;
	
	public function apply($html) {
		return $this->getPurifier()->purify($html);
	}
	
	/**
	 * @return	\HTMLPurifier
	 */
	protected function getPurifier() {
		if (self::$purifier === null) {
			$config = \HTMLPurifier_Config::createDefault();
			$this->setAttributeDefinitions($config);
			self::$purifier = new \HTMLPurifier($config);
		}
		
		return self::$purifier;
	}
	
	protected function setAttributeDefinitions(\HTMLPurifier_Config $config) {
		// TODO: move this into own PHP classes
		$definition = $config->getHTMLDefinition(true);
		$definition->addAttribute('blockquote', 'data-quote-title', 'Text');
		$definition->addAttribute('blockquote', 'data-quote-url', 'URI');
		
		$definition->addElement('woltlab-mention', 'Inline', 'Inline', '', [
			'data-user-id' => 'Number',
			'data-username' => 'Text'
		]);
		
		$definition->addElement('woltlab-metacode', 'Inline', 'Inline', '', [
			'data-attributes' => 'Text',
			'data-name' => 'Text'
		]);
		
		$definition->addElement('woltlab-metacode-marker', 'Inline', 'Empty', '', [
			'data-attributes' => 'Text',
			'data-name' => 'Text',
			'data-uuid' => 'Text'
		]);
	}
}
