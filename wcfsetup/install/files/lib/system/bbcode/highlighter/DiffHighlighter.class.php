<?php
namespace wcf\system\bbcode\highlighter;
use wcf\util\StringUtil;

/**
 * Highlights difference files.
 * 
 * @author	Tim Duesterhus
 * @copyright	2011 Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.bbcode.highlighter
 * @category	Community Framework
 */
class DiffHighlighter extends Highlighter {
	/**
	 * keywords for an added line, the + is used in unified diffs, the > in
	 * normal diffs
	 * @var	array<string>
	 */
	protected $add = array("+", ">");
	
	/**
	 * keywords for an deleted line, the - is used in unified diff, the < in
	 * normal diffs
	 * @var	array<string>
	 */
	protected $delete = array("-", "<");
	
	/**
	 * splitter in changes for normal diff
	 * @var	array<string>
	 */
	protected $splitter = array("---");
	
	/**
	 * keywords for the line info, the @ is used in unified diffs, the numbers
	 * in normal diffs
	 * @var	array<string>
	 */
	protected $info = array("@", '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	
	/**
	 * @see	\wcf\system\bbcode\highlighter\Highlighter::highlight()
	 */
	public function highlight($data) {
		$lines = explode("\n", $data);
		foreach ($lines as $key => $val) {
			if (in_array(mb_substr($val, 0, 1), $this->info) || in_array($val, $this->splitter)) {
				$lines[$key] = '<span class="hlComments">'.StringUtil::encodeHTML($val).'</span>';
			}
			else if (in_array(mb_substr($val, 0, 1), $this->add)) {
				$lines[$key] = '<span class="hlAdded">'.StringUtil::encodeHTML($val).'</span>';
			}
			else if (in_array(mb_substr($val, 0, 1), $this->delete)) {
				$lines[$key] = '<span class="hlRemoved">'.StringUtil::encodeHTML($val).'</span>';
			}
			else {
				$lines[$key] = StringUtil::encodeHTML($val);
			}
		}
		
		$data = implode("\n", $lines);
		return $data;
	}
}
