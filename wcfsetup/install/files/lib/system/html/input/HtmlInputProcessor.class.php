<?php
namespace wcf\system\html\input;
use wcf\system\bbcode\HtmlBBCodeParser;
use wcf\system\html\input\filter\IHtmlInputFilter;
use wcf\system\html\input\filter\MessageHtmlInputFilter;
use wcf\system\html\input\node\HtmlInputNodeProcessor;
use wcf\system\html\AbstractHtmlProcessor;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Reads a HTML string, applies filters and parses all nodes including bbcodes.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2016 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Input
 * @since       3.0
 */
class HtmlInputProcessor extends AbstractHtmlProcessor {
	/**
	 * list of embedded content grouped by type
	 * @var array
	 */
	protected $embeddedContent = [];
	
	/**
	 * @var	IHtmlInputFilter
	 */
	protected $htmlInputFilter;
	
	/**
	 * @var HtmlInputNodeProcessor
	 */
	protected $htmlInputNodeProcessor;
	
	/**
	 * Processes the input html string.
	 *
	 * @param       string          $html           html string
	 * @param       string          $objectType     object type identifier
	 * @param       integer         $objectID       object id
	 */
	public function process($html, $objectType, $objectID = 0) {
		$this->setContext($objectType, $objectID);
		
		// enforce consistent newlines
		$html = StringUtil::trim(StringUtil::unifyNewlines($html));
		
		// check if this is true HTML or just a bbcode string
		$html = $this->convertToHtml($html);
		
		// transform bbcodes into metacode markers
		$html = HtmlBBCodeParser::getInstance()->parse($html);
		
		// filter HTML
		$html = $this->getHtmlInputFilter()->apply($html);
		
		// pre-parse HTML
		$this->getHtmlInputNodeProcessor()->load($this, $html);
		$this->getHtmlInputNodeProcessor()->process();
		$this->embeddedContent = $this->getHtmlInputNodeProcessor()->getEmbeddedContent();
	}
	
	/**
	 * Processes only embedded content. This method should only be called when rebuilding
	 * data where only embedded content is relevant, but no actual parsing is required.
	 * 
	 * @param       string          $html           html string
	 * @param       string          $objectType     object type identifier
	 * @param       integer         $objectID       object id
	 * @throws      \UnexpectedValueException
	 */
	public function processEmbeddedContent($html, $objectType, $objectID) {
		if (!$objectID) {
			throw new \UnexpectedValueException("Object id parameter must be non-zero.");
		}
		
		$this->setContext($objectType, $objectID);
		
		$this->getHtmlInputNodeProcessor()->load($this, $html);
		$this->getHtmlInputNodeProcessor()->processEmbeddedContent();
		$this->embeddedContent = $this->getHtmlInputNodeProcessor()->getEmbeddedContent();
	}
	
	/**
	 * Checks the input html for disallowed bbcodes and returns any matches.
	 * 
	 * @return      string[]        list of matched disallowed bbcodes
	 */
	public function validate() {
		return $this->getHtmlInputNodeProcessor()->validate();
	}
	
	/**
	 * Returns the parsed HTML ready to store.
	 * 
	 * @return      string  parsed html
	 */
	public function getHtml() {
		return $this->getHtmlInputNodeProcessor()->getHtml();
	}
	
	/**
	 * Returns the raw text content of current document.
	 * 
	 * @return      string          raw text content
	 */
	public function getTextContent() {
		return $this->getHtmlInputNodeProcessor()->getTextContent();
	}
	
	/**
	 * Returns the all embedded content data.
	 *
	 * @return array
	 */
	public function getEmbeddedContent() {
		return $this->embeddedContent;
	}
	
	/**
	 * @return HtmlInputNodeProcessor
	 */
	public function getHtmlInputNodeProcessor() {
		if ($this->htmlInputNodeProcessor === null) {
			$this->htmlInputNodeProcessor = new HtmlInputNodeProcessor();
		}
		
		return $this->htmlInputNodeProcessor;
	}
	
	/**
	 * Sets the new object id.
	 * 
	 * @param       integer         $objectID       object id
	 */
	public function setObjectID($objectID) {
		$this->context['objectID'] = $objectID;
	}
	
	/**
	 * @return	IHtmlInputFilter
	 */
	protected function getHtmlInputFilter() {
		if ($this->htmlInputFilter === null) {
			$this->htmlInputFilter = new MessageHtmlInputFilter();
		}
		
		return $this->htmlInputFilter;
	}
	
	/**
	 * Converts bbcodes using newlines into valid HTML.
	 * 
	 * @param       string          $html           html string
	 * @return      string          parsed html string
	 */
	protected function convertToHtml($html) {
		if (!preg_match('~^<[a-zA-Z\-]+~', $html) || !preg_match('~</[a-zA-Z\-]>$~', $html)) {
			$html = StringUtil::encodeHTML($html);
			$parts = preg_split('~(\n+)~', $html, null, PREG_SPLIT_DELIM_CAPTURE);
			
			$openParagraph = false;
			$html = '';
			for ($i = 0, $length = count($parts); $i < $length; $i++) {
				$part = $parts[$i];
				if (strpos($part, "\n") !== false) {
					$newlines = substr_count($part, "\n");
					if ($newlines === 1) {
						$html .= '<br>';
					}
					else {
						if ($openParagraph) {
							$html .= '</p>';
							$openParagraph = false;
						}
						
						// ignore two newline because a new paragraph with bbcodes is created
						// using two subsequent newlines
						$newlines -= 2;
						if ($newlines === 0) {
							continue;
						}
						
						$html .= str_repeat('<p><br></p>', $newlines);
					}
				}
				else {
					if (!$openParagraph) {
						$html .= '<p>';
					}
					
					$html .= $part;
					$openParagraph = true;
				}
			}
			
			$html .= '</p>';
		}
		
		return $html;
	}
}
