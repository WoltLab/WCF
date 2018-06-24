<?php
declare(strict_types=1);
namespace wcf\system\html\output\node;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNodeProcessor;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\DOMUtil;
use wcf\util\StringUtil;

/**
 * Processes quotes.
 * 
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Html\Output\Node
 * @since       3.0
 */
class HtmlOutputNodeWoltlabQuote extends AbstractHtmlOutputNode {
	/**
	 * @inheritDoc
	 */
	protected $tagName = 'woltlab-quote';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, AbstractHtmlNodeProcessor $htmlNodeProcessor) {
		/** @var \DOMElement $element */
		foreach ($elements as $element) {
			switch ($this->outputType) {
				case 'text/html':
					$collapse = false;
					
					// try to predict long content
					if ($element->getElementsByTagName('p')->length > 5 || $element->getElementsByTagName('br')->length > 5) {
						$collapse = true;
					}
					
					$link = $element->getAttribute('data-link');
					if (mb_strpos($link, 'index.php') === 0) {
						$link = WCF::getPath() . $link;
					}
					
					$nodeIdentifier = StringUtil::getRandomID();
					$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
						'author' => $element->getAttribute('data-author'),
						'collapse' => $collapse,
						'url' => $link
					]);
					
					$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
					break;
				
				case 'text/simplified-html':
				case 'text/plain':
					// check if this quote is within another
					if (DOMUtil::hasParent($element, 'woltlab-quote')) {
						DOMUtil::removeNode($element);
					}
					else {
						$htmlNodeProcessor->replaceElementWithText(
							$element,
							WCF::getLanguage()->getDynamicVariable('wcf.bbcode.quote.simplified', ['cite' => $element->getAttribute('data-author')]),
							true
						);
					}
					break;
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function replaceTag(array $data) {
		$externalQuoteLink = (!empty($data['url'])) ? !ApplicationHandler::getInstance()->isInternalURL($data['url']) : false;
		if (!$externalQuoteLink) {
			$data['url'] = preg_replace('~^https://~', RouteHandler::getProtocol(), $data['url']);
		}
		
		$quoteAuthorObject = null;
		if ($data['author'] && !$externalQuoteLink) {
			$quoteAuthorLC = mb_strtolower(StringUtil::decodeHTML($data['author']));
			foreach (MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.quote') as $user) {
				if (mb_strtolower($user->username) == $quoteAuthorLC) {
					$quoteAuthorObject = $user;
					break;
				}
			}
		}
		
		WCF::getTPL()->assign([
			'collapseQuote' => $data['collapse'],
			'quoteLink' => $data['url'],
			'quoteAuthor' => $data['author'],
			'quoteAuthorObject' => $quoteAuthorObject,
			'isExternalQuoteLink' => $externalQuoteLink
		]);
		return WCF::getTPL()->fetch('quoteMetaCode');
	}
}
