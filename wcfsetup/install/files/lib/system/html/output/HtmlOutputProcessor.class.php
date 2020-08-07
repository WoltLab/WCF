<?php
namespace wcf\system\html\output;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\html\output\node\HtmlOutputNodeProcessor;
use wcf\system\html\AbstractHtmlProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Processes stored HTML for final display.
 * 
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output
 * @since       3.0
 */
class HtmlOutputProcessor extends AbstractHtmlProcessor {
	/**
	 * Generate the table of contents, implicitly enable this for certain object types on demand.
	 * @var bool|null
	 * @since	5.2
	 */
	public $enableToc;
	
	/**
	 * Removes any link contained inside the message text.
	 * @var bool
	 * @since 5.2
	 */
	public $removeLinks = false;
	
	/**
	 * output node processor instance
	 * @var	HtmlOutputNodeProcessor
	 */
	protected $htmlOutputNodeProcessor;
	
	/**
	 * content language id
	 * @var integer
	 */
	protected $languageID;
	
	/**
	 * desired output type
	 * @var string
	 */
	protected $outputType = 'text/html';
	
	/**
	 * enables rel=ugc for external links
	 * @var bool
	 */
	public $enableUgc = true;
	
	/**
	 * Processes the input html string.
	 *
	 * @param       string          $html                           html string
	 * @param       string          $objectType                     object type identifier
	 * @param       integer         $objectID                       object id
	 * @param	boolean		$doKeywordHighlighting          enable keyword highlighting
	 * @param       integer         $languageID                     content language id
	 */
	public function process($html, $objectType, $objectID, $doKeywordHighlighting = true, $languageID = null) {
		$this->languageID = $languageID;
		$this->setContext($objectType, $objectID);
		
		$this->getHtmlOutputNodeProcessor()->setOutputType($this->outputType);
		$this->getHtmlOutputNodeProcessor()->enableKeywordHighlighting($doKeywordHighlighting);
		$this->getHtmlOutputNodeProcessor()->load($this, $html);
		$this->getHtmlOutputNodeProcessor()->process();
	}
	
	/**
	 * Sets the desired output type.
	 * 
	 * @param       string          $outputType     desired output type
	 * @throws      \InvalidArgumentException
	 */
	public function setOutputType($outputType) {
		if (!in_array($outputType, ['text/html', 'text/simplified-html', 'text/plain'])) {
			throw new \InvalidArgumentException("Expected 'text/html', 'text/simplified-html' or 'text/plain', but received '" . $outputType . "'");
		}
		
		$this->outputType = $outputType;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getHtml() {
		return $this->getHtmlOutputNodeProcessor()->getHtml();
	}
	
	/**
	 * @inheritdoc
	 * @throws \InvalidArgumentException
	 */
	public function setContext($objectType, $objectID) {
		parent::setContext($objectType, $objectID);
		
		MessageEmbeddedObjectManager::getInstance()->setActiveMessage($objectType, $objectID, $this->languageID);
		$objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.message', $objectType);
		if ($this->enableToc === null) {
			$this->enableToc = (!empty($objectType->additionalData['enableToc']));
		}
	}
	
	/**
	 * Returns the output node processor instance.
	 * 
	 * @return      HtmlOutputNodeProcessor         output node processor instance
	 */
	protected function getHtmlOutputNodeProcessor() {
		if ($this->htmlOutputNodeProcessor === null) {
			$this->htmlOutputNodeProcessor = new HtmlOutputNodeProcessor();
		}
		
		return $this->htmlOutputNodeProcessor;
	}
}
