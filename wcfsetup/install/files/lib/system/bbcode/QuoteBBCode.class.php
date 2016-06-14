<?php
namespace wcf\system\bbcode;
use wcf\system\application\ApplicationHandler;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Parses the [quote] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class QuoteBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {
			$quoteLink = (!empty($openingTag['attributes'][1]) ? $openingTag['attributes'][1] : '');
			$externalQuoteLink = (!empty($openingTag['attributes'][1]) ? !ApplicationHandler::getInstance()->isInternalURL($openingTag['attributes'][1]) : false);
			if (!$externalQuoteLink) {
				$quoteLink = preg_replace('~^https?://~', RouteHandler::getProtocol(), $quoteLink);
			}
			$quoteAuthor = (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : '');
			$quoteAuthorObject = null;
			if ($quoteAuthor && !$externalQuoteLink) {
				$quoteAuthorLC = mb_strtolower(StringUtil::decodeHTML($quoteAuthor));
				foreach (MessageEmbeddedObjectManager::getInstance()->getObjects('com.woltlab.wcf.quote') as $user) {
					if (mb_strtolower($user->username) == $quoteAuthorLC) {
						$quoteAuthorObject = $user;
						break;
					}
				}
			}
			
			WCF::getTPL()->assign([
				'content' => $content,
				'quoteLink' => $quoteLink,
				'quoteAuthor' => $quoteAuthor,
				'quoteAuthorObject' => $quoteAuthorObject,
				'isExternalQuoteLink' => $externalQuoteLink
			]);
			return WCF::getTPL()->fetch('quoteBBCodeTag');
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			return WCF::getLanguage()->getDynamicVariable('wcf.bbcode.quote.text', ['content' => $content, 'cite' => (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : '')])."\n";
		}
	}
}
