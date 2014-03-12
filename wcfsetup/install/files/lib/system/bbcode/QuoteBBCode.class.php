<?php
namespace wcf\system\bbcode;
use wcf\system\application\ApplicationHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;

/**
 * Parses the [quote] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class QuoteBBCode extends AbstractBBCode {
	/**
	 * @see	\wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {
			$quoteLink = (!empty($openingTag['attributes'][1]) ? $openingTag['attributes'][1] : '');
			$externalQuoteLink = (!empty($openingTag['attributes'][1]) ? !ApplicationHandler::getInstance()->isInternalURL($openingTag['attributes'][1]) : false);
			if (!$externalQuoteLink) {
				$quoteLink = preg_replace('~^https?://~', RouteHandler::getProtocol(), $quoteLink);
			}
			
			WCF::getTPL()->assign(array(
				'content' => $content,
				'quoteLink' => $quoteLink,
				'quoteAuthor' => (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : ''),
				'isExternalQuoteLink' => $externalQuoteLink
			));
			return WCF::getTPL()->fetch('quoteBBCodeTag');
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			return WCF::getLanguage()->getDynamicVariable('wcf.bbcode.quote.text', array('content' => $content, 'cite' => (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : '')));
		}
	}
}
