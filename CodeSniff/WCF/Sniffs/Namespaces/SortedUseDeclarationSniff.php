<?php
/**
 * This sniff is based on Squiz_Sniffs_Classes_ClassFileNameSniff. Originally written
 * by Greg Sherwood <gsherwood@squiz.net> and released under the terms of the BSD Licence.
 * See: https://github.com/squizlabs/PHP_CodeSniffer/blob/master/CodeSniffer/Standards/PSR2/Sniffs/Namespaces/UseDeclarationSniff.php
 * 
 * @author	Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
class WCF_Sniffs_Namespaces_SortedUseDeclarationSniff implements PHP_CodeSniffer_Sniff {
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array(T_OPEN_TAG);
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int				  $stackPtr  The position of the current token in
	 *										the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr) {
		$tokens = $phpcsFile->getTokens();
		
		$classes = array();
		while (true) {
			$stackPtr = $phpcsFile->findNext(T_USE, ($stackPtr + 1));
			if ($stackPtr === false) break;
			
			// Ignore USE keywords inside closures.
			$next = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
			if ($tokens[$next]['code'] === T_OPEN_PARENTHESIS) {
				break;
			}
			$start = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), ($stackPtr + 1));
			$end = $phpcsFile->findNext(array(T_NS_SEPARATOR, T_STRING), ($start + 1), null, true);
			$class = '';
			for ($i = $start; $i < $end; $i++) {
				$class .= $tokens[$i]['content'];
			}
			$classes[$stackPtr] = $class;
		}
		$previous = '';
		foreach ($classes as $stackPtr => $class) {
			if ($previous !== '') {
				if ($class == $previous) {
					$error = 'Using class %s twice;';
					$data = array($class);
					$phpcsFile->addError($error, $stackPtr, 'DuplicateUse', $data);
					
				}
				else if (!$this->compare($previous, $class)) {
					$error = 'Uses are misordered; check %s and %s';
					$data = array($previous, $class);
					$phpcsFile->addError($error, $stackPtr, 'MisorderedUse', $data);
				}
			}
			$previous = $class;
		}
	}
	
	public function compare($classA, $classB) {
		if ($classA == $classB) return true;
		
		$classA = explode('\\', $classA);
		$classB = explode('\\', $classB);
		
		for ($i = 0, $max = min(count($classA), count($classB)); $i < $max; $i++) {
			if ($classA[$i] == $classB[$i]) {
				unset($classA[$i], $classB[$i]);
			}
		}
		$classA = array_values($classA);
		$classB = array_values($classB);
		
		$classALength = count($classA);
		$classBLength = count($classB);
		for ($i = 0, $max = min($classALength, $classBLength); $i < $max; $i++) {
			if ($i + 1 === $classBLength && $i + 1 !== $classALength) return true;
			if ($i + 1 !== $classBLength && $i + 1 === $classALength) return false;
			
			if (strcasecmp($classA[$i], $classB[$i]) < 0) {
				return true;
			}
		}
		return false;
	}
}
