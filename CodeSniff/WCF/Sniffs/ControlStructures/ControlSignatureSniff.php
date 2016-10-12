<?php
if (class_exists('PHP_CodeSniffer_Standards_AbstractPatternSniff', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractPatternSniff not found');
}

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
class WCF_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff {
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
