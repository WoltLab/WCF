<?php
namespace wcf\system\bbcode;
use wcf\system\Regex;
use wcf\util\StringUtil;

/**
 * Parses the [table] bbcode tag.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.bbcode
 * @subpackage	system.bbcode
 * @category	Community Framework
 */
class TableBBCode extends AbstractBBCode {
	/**
	 * @see	wcf\system\bbcode\IBBCode::getParsedTag()
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {
			$parsedContent = Regex::compile('(?:\s|<br />)*(\[tr\].*\[/tr\])(?:\s|<br />)*', Regex::CASE_INSENSITIVE | Regex::DOT_ALL)->replace($content, '\\1');
			
			// check syntax
			$regex = new Regex('\[/?t[rd]\]', Regex::CASE_INSENSITIVE);
			if ($regex->match($parsedContent, true)) {
				$matches = $regex->getMatches();
				
				$openTags = array();
				$openTDs = 0;
				$firstRowTDs = 0;
				
				// parse tags
				foreach ($matches[0] as $match) {
					switch ($match) {
						case '[td]':
							if (end($openTags) !== '[tr]') return;
							$openTags[] = $match;
							$openTDs++;
						break;
						case '[/td]':
							if (end($openTags) !== '[td]') return;
							array_pop($openTags);
						break;
						case '[tr]':
							if (!empty($openTags)) return;
							$openTags[] = $match;
						break;
						case '[/tr]':
							if (end($openTags) !== '[tr]') return;
							
							array_pop($openTags);
							
							// check that every row has got the same number of tds
							if ($firstRowTDs === 0) $firstRowTDs = $openTDs;
							if ($openTDs !== $firstRowTDs) return;
							
							$openTDs = 0;
						break;
					}
				}
				
				if (!empty($openTags)) return;
			}
			else {
				return '';
			}
			
			// tr
			$parsedContent = Regex::compile('\[tr\](?:\s|<br />)*', Regex::CASE_INSENSITIVE)->replace($parsedContent, '<tr>');
			// td
			$parsedContent = StringUtil::replaceIgnoreCase('[td]', '<td>', $parsedContent);
			// /td
			$parsedContent = Regex::compile('\[/td\](?:\s|<br />)*', Regex::CASE_INSENSITIVE)->replace($parsedContent, '</td>');
			// /tr
			$parsedContent = Regex::compile('\[/tr\](?:\s|<br />)*', Regex::CASE_INSENSITIVE)->replace($parsedContent, '</tr>');
			
			return '<div class="container bbcodeTable"><table class="table"><tbody>'.$parsedContent.'</tbody></table></div>';
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			// remove table tags
			$content = StringUtil::replaceIgnoreCase('[td]', '* ', $content);
			$content = StringUtil::replaceIgnoreCase('[/td]', ' ', $content);
			$content = StringUtil::replaceIgnoreCase('[tr]', '', $content);
			$content = StringUtil::replaceIgnoreCase('[/tr]', '', $content);
			
			return $content;
		}
	}
}
