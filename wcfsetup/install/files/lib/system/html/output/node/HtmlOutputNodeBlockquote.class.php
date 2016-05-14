<?php
namespace wcf\system\html\output\node;
use wcf\system\application\ApplicationHandler;
use wcf\system\html\node\AbstractHtmlNode;
use wcf\system\html\node\HtmlNodeProcessor;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * TOOD documentation
 * @since	2.2
 */
class HtmlOutputNodeBlockquote extends AbstractHtmlNode {
	protected $tagName = 'blockquote';
	
	/**
	 * @inheritDoc
	 */
	public function process(array $elements, HtmlNodeProcessor $htmlNodeProcessor) {
		foreach ($elements as $element) {
			if ($element->getAttribute('class') === 'quoteBox') {
				$nodeIdentifier = StringUtil::getRandomID();
				$htmlNodeProcessor->addNodeData($this, $nodeIdentifier, [
					'title' => ($element->hasAttribute('data-quote-title')) ? $element->getAttribute('data-quote-title') : '',
					'url' => ($element->hasAttribute('data-quote-url')) ? $element->getAttribute('data-quote-url') : ''
				]);
				
				$htmlNodeProcessor->renameTag($element, 'wcfNode-' . $nodeIdentifier);
			}
			else {
				$htmlNodeProcessor->unwrapContent($element);
			}
		}
	}
	
	public function replaceTag(array $data) {
		$externalQuoteLink = (!empty($data['url'])) ? !ApplicationHandler::getInstance()->isInternalURL($data['url']) : false;
		if (!$externalQuoteLink) {
			$data['url'] = preg_replace('~^https://~', RouteHandler::getProtocol(), $data['url']);
		}
		
		$quoteAuthorObject = null;
		/*
		 * TODO: how should the author object be resolved?
		 * 
		if ($quoteAuthor && !$externalQuoteLink) {
			$quoteAuthorLC = mb_strtolower(StringUtil::decodeHTML($quoteAuthor));
			foreach (MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.quote') as $user) {
				if (mb_strtolower($user->username) == $quoteAuthorLC) {
					$quoteAuthorObject = $user;
					break;
				}
			}
		}
		*/
		
		WCF::getTPL()->assign([
			'quoteLink' => $data['url'],
			'quoteAuthor' => $data['title'],
			'quoteAuthorObject' => $quoteAuthorObject,
			'isExternalQuoteLink' => $externalQuoteLink
		]);
		return WCF::getTPL()->fetch('quoteMetaCode');
	}
}
