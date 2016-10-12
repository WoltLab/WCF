<?php
/**
 * This sniff is based on Squiz_Sniffs_Classes_ClassFileNameSniff. Originally written
 * by Greg Sherwood <gsherwood@squiz.net> and Marc McIntyre <mmcintyre@squiz.net>
 * and released under the terms of the BSD Licence.
 * See: https://github.com/squizlabs/PHP_CodeSniffer/blob/master/CodeSniffer/Standards/Squiz/Sniffs/Classes/ClassFileNameSniff.php
 * 
 * @author	Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
class WCF_Sniffs_Classes_ClassFileNameSniff implements PHP_CodeSniffer_Sniff {
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_CLASS,
			T_INTERFACE,
			T_TRAIT,
		);
	}
	
	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens   = $phpcsFile->getTokens();
		$decName  = $phpcsFile->findNext(T_STRING, $stackPtr);
		$fullPath = basename($phpcsFile->getFilename());
		$fileName = substr($fullPath, 0, strpos($fullPath, '.'));
		
		if ($tokens[$decName]['content'] !== $fileName) {
			$error = '%s name doesn\'t match filename; expected "%s %s"';
			$data = array(
				ucfirst($tokens[$stackPtr]['content']),
				$tokens[$stackPtr]['content'],
				$fileName,
			);
			$phpcsFile->addError($error, $stackPtr, 'NoMatch', $data);
		}
	}
}
