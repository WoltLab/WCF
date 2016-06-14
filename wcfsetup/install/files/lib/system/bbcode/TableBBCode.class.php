<?php
namespace wcf\system\bbcode;
use wcf\system\Regex;

/**
 * Parses the [table] bbcode tag.
 * 
 * @author	Tim Duesterhus, Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Bbcode
 */
class TableBBCode extends AbstractBBCode {
	/**
	 * @inheritDoc
	 */
	public function getParsedTag(array $openingTag, $content, array $closingTag, BBCodeParser $parser) {
		if ($parser->getOutputType() == 'text/html') {
			$parsedContent = Regex::compile('(?:\s|<br>)*(\[tr\].*\[/tr\])(?:\s|<br>)*', Regex::CASE_INSENSITIVE | Regex::DOT_ALL)->replace($content, '\\1');
			
			// check syntax
			$regex = new Regex('\[/?t[rd]\]', Regex::CASE_INSENSITIVE);
			if ($regex->match($parsedContent, true)) {
				$matches = $regex->getMatches();
				
				$openTags = [];
				$openTDs = 0;
				$firstRowTDs = 0;
				
				// parse tags
				foreach ($matches[0] as $match) {
					switch ($match) {
						case '[td]':
							if (end($openTags) !== '[tr]') return '';
							$openTags[] = $match;
							$openTDs++;
						break;
						case '[/td]':
							if (end($openTags) !== '[td]') return '';
							array_pop($openTags);
						break;
						case '[tr]':
							if (!empty($openTags)) return '';
							$openTags[] = $match;
						break;
						case '[/tr]':
							if (end($openTags) !== '[tr]') return '';
							
							array_pop($openTags);
							
							// check that every row has got the same number of tds
							if ($firstRowTDs === 0) $firstRowTDs = $openTDs;
							if ($openTDs !== $firstRowTDs) return '';
							
							$openTDs = 0;
						break;
					}
				}
				
				if (!empty($openTags)) return '';
			}
			else {
				return '';
			}
			
			// tr
			$parsedContent = Regex::compile('\[tr\](?:\s|<br>)*', Regex::CASE_INSENSITIVE)->replace($parsedContent, '<tr>');
			// td
			$parsedContent = str_ireplace('[td]', '<td>', $parsedContent);
			// /td
			$parsedContent = Regex::compile('\[/td\](?:\s|<br>)*', Regex::CASE_INSENSITIVE)->replace($parsedContent, '</td>');
			// /tr
			$parsedContent = Regex::compile('\[/tr\](?:\s|<br>)*', Regex::CASE_INSENSITIVE)->replace($parsedContent, '</tr>');
			
			return '<div class="container bbcodeTable"><table class="table responsiveTable"><tbody>'.$parsedContent.'</tbody></table></div>';
		}
		else if ($parser->getOutputType() == 'text/simplified-html') {
			// remove table tags
			$content = str_ireplace('[td]', '* ', $content);
			$content = str_ireplace('[/td]', ' ', $content);
			$content = str_ireplace('[tr]', '', $content);
			$content = str_ireplace('[/tr]', '', $content);
			
			return $content;
		}
		
		return '';
	}
}
