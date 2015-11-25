<?php
namespace wcf\system\html\output\node;

use wcf\system\application\ApplicationHandler;
use wcf\system\html\output\HtmlOutputNodeProcessor;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

class HtmlOutputNodeBlockquote implements IHtmlOutputNode {
	public function process(HtmlOutputNodeProcessor $htmlOutputNodeProcessor) {
		$elements = $htmlOutputNodeProcessor->getDocument()->getElementsByTagName('blockquote');
		while ($elements->length) {
			/** @var \DOMElement $blockquote */
			$blockquote = $elements->item(0);
			
			if ($blockquote->getAttribute('class') === 'quoteBox') {
				$nodeIdentifier = StringUtil::getRandomID();
				$htmlOutputNodeProcessor->addNodeData($this, $nodeIdentifier, [
					'title' => ($blockquote->hasAttribute('data-quote-title')) ? $blockquote->getAttribute('data-quote-title') : '',
					'url' => ($blockquote->hasAttribute('data-quote-url')) ? $blockquote->getAttribute('data-quote-url') : ''
				]);
				
				$htmlOutputNodeProcessor->renameTag($blockquote, 'wcfNode-' . $nodeIdentifier);
			}
			else {
				$htmlOutputNodeProcessor->unwrapContent($blockquote);
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