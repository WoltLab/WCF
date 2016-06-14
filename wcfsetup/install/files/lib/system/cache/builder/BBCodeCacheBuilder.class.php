<?php
namespace wcf\system\cache\builder;
use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\bbcode\BBCode;
use wcf\system\WCF;

/**
 * Caches the bbcodes.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class BBCodeCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	protected function rebuild(array $parameters) {
		$attributes = [];
		$data = ['bbcodes' => [], 'highlighters' => []];
		
		// get attributes
		$sql = "SELECT		attribute.*, bbcode.bbcodeTag
			FROM		wcf".WCF_N."_bbcode_attribute attribute
			LEFT JOIN	wcf".WCF_N."_bbcode bbcode
			ON		(bbcode.bbcodeID = attribute.bbcodeID)
			WHERE		bbcode.isDisabled = 0
			ORDER BY	attribute.attributeNo";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			if (!isset($attributes[$row['bbcodeTag']])) $attributes[$row['bbcodeTag']] = [];
			
			$attributes[$row['bbcodeTag']][$row['attributeNo']] = new BBCodeAttribute(null, $row);
		}
		
		// get bbcodes
		$sql = "SELECT	*
			FROM	wcf".WCF_N."_bbcode
			WHERE	isDisabled = 0";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$row['attributes'] = (isset($attributes[$row['bbcodeTag']]) ? $attributes[$row['bbcodeTag']] : []);
			$data['bbcodes'][$row['bbcodeTag']] = new BBCode(null, $row);
		}
		
		// get code highlighters
		$highlighters = glob(WCF_DIR . 'lib/system/bbcode/highlighter/*.class.php');
		if (is_array($highlighters)) {
			foreach ($highlighters as $highlighter) {
				if (preg_match('~\/([a-zA-Z]+)Highlighter\.class\.php$~', $highlighter, $matches)) {
					$data['highlighters'][] = strtolower($matches[1]);
				}
			}
		}
		
		return $data;
	}
}
