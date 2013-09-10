<?php
namespace wcf\system\bbcode;
use wcf\system\application\ApplicationHandler;
use wcf\system\WCF;

/**
 * Parses the [quote] bbcode tag.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class QuoteBBCode extends AbstractBBCode {
	/**
	 * @see	wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {
			WCF::getTPL()->assign(array(
				'content' => $content,
				'quoteLink' => (!empty($openingTag['attributes'][1]) ? $openingTag['attributes'][1] : ''),
				'quoteAuthor' => (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : ''),
				'isExternalQuoteLink' => (!empty($openingTag['attributes'][1]) ? !ApplicationHandler::getInstance()->isInternalURL($openingTag['attributes'][1]) : false)
			));
			return WCF::getTPL()->fetch('quoteBBCodeTag');
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			return WCF::getLanguage()->getDynamicVariable('wcf.bbcode.quote.text', array('content' => $content, 'cite' => (!empty($openingTag['attributes'][0]) ? $openingTag['attributes'][0] : '')));
		}
	}
}
