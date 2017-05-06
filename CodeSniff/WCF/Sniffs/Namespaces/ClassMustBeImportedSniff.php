<?php
namespace WCF\Sniffs\Namespaces;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Disallows calling non global classes via FQN. Classes must be imported with use [...];
 * 
 * @author	Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
class ClassMustBeImportedSniff implements Sniff {
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(
			T_NS_SEPARATOR
		);
	}
	
	/**
	 * Processes this sniff, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int                  $stackPtr  The position of the current token in the
	 *                                        stack passed in $tokens.
	 */
	public function process(File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		
		// skip files in global namespace
		if ($phpcsFile->findPrevious(T_NAMESPACE, $stackPtr) === false) {
			return;
		}
		
		// no use found
		if ($phpcsFile->findPrevious(array(T_NAMESPACE, T_USE), ($stackPtr - 1), null, false, null, true) === false) {
			// find previous class part and non class part
			$prevClassPart = $phpcsFile->findPrevious(array(T_STRING, T_NS_SEPARATOR), $stackPtr - 1);
			$prevNonClassPart = $phpcsFile->findPrevious(array(T_STRING, T_NS_SEPARATOR), $stackPtr - 1, null, true);
			
			// backslash as the very first character of the class is allowed (global classes)
			if ($prevClassPart >= $prevNonClassPart) {
				$nextNonClassPart = $phpcsFile->findNext(array(T_STRING, T_NS_SEPARATOR), $stackPtr + 1, null, true);
				$lastNSSep = $phpcsFile->findPrevious(T_NS_SEPARATOR, $nextNonClassPart);
				
				// check whether we are the last backslash (no multiple reporting)
				if ($lastNSSep === $stackPtr) {
					$start = $phpcsFile->findPrevious(array(T_NS_SEPARATOR, T_STRING), $stackPtr - 1, null, true) + 1;
					$end = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), ($start + 1), null, true);
					$class = '';
					for ($i = $start; $i < $end; $i++) {
						$class .= $tokens[$i]['content'];
					}
					
					$tClass = $phpcsFile->findPrevious(array(T_CLASS), $stackPtr - 1);
					// are we trying to extend a class with the same name?
					if ($tClass !== false) {
						$newClass = $phpcsFile->findNext(T_STRING, $tClass);
						if ($tokens[$newClass]['content'] == $tokens[$end - 1]['content']) return;
					}
					
					$error = 'Namespaced classes (%s) must be imported with use.';
					$data = array(
						$class
					);
					$phpcsFile->addError($error, $stackPtr, 'MustBeImported', $data);
				}
			}
		}
	}
}