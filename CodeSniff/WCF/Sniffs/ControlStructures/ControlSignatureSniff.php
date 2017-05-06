<?php
namespace WCF\Sniffs\ControlStructures;

use PHP_CodeSniffer\Sniffs\AbstractPatternSniff;
use PHP_CodeSniffer\Files\File;

/**
 * This sniff is based on Squiz_Sniffs_ControlStructures_ControlSignatureSniff. Originally written
 * by Greg Sherwood <gsherwood@squiz.net> and Marc McIntyre <mmcintyre@squiz.net>
 * and released under the terms of the BSD Licence.
 * See: https://github.com/squizlabs/PHP_CodeSniffer/blob/master/CodeSniffer/Standards/Squiz/Sniffs/ControlStructures/ControlSignatureSniff.php
 * 
 * @author	Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
class ControlSignatureSniff extends AbstractPatternSniff {
	/**
	 * A list of tokenizers this sniff supports.
	 *
	 * @var array
	 */
	public $supportedTokenizers = array(
		'PHP',
		'JS'
	);
	
	/**
	 * Returns the patterns that this test wishes to verify.
	 *
	 * @return array(string)
	 */
	protected function getPatterns() {
		return array(
			'try {EOL...}EOL...catch (...) {',
			'do {EOL...}EOL...while (...);EOL',
			'while (...) {EOL',
			'for (...) {EOL',
			'if (...) {EOL',
			'foreach (...) {EOL',
			'}EOL...else if (...) {EOL',
			'}EOL...elseif (...) {EOL',
			'}EOL...else {EOL',
		);
	}
}
