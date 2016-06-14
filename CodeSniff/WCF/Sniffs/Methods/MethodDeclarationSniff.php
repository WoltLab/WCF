<?php
/**
 * This sniff is based on PSR2_Sniffs_Methods_MethodDeclarationSniff. Originally written
 * by Greg Sherwood <gsherwood@squiz.net> and released under the terms of the BSD Licence.
 * See: https://github.com/squizlabs/PHP_CodeSniffer/blob/master/CodeSniffer/Standards/PSR2/Sniffs/Methods/MethodDeclarationSniff.php
 *
 * @author	Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
class WCF_Sniffs_Methods_MethodDeclarationSniff extends PHP_CodeSniffer_Standards_AbstractScopeSniff {
	/**
	 * Constructs a Squiz_Sniffs_Scope_MethodScopeSniff.
	 */
	public function __construct() {
		parent::__construct(array(T_CLASS, T_INTERFACE), array(T_FUNCTION));
	}
	
	/**
	 * Processes the function tokens within the class.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
	 * @param int                  $stackPtr  The position where the token was found.
	 * @param int                  $currScope The current scope opener token.
	 *
	 * @return void
	 */
	protected function processTokenWithinScope(PHP_CodeSniffer_File $phpcsFile, $stackPtr, $currScope) {
		$tokens = $phpcsFile->getTokens();
		
		$methodName = $phpcsFile->getDeclarationName($stackPtr);
		if ($methodName === null) {
			// Ignore closures.
			return;
		}
		
		$visibility = 0;
		$static = 0;
		$abstract = 0;
		$final = 0;
		
		$find = PHP_CodeSniffer_Tokens::$methodPrefixes;
		$find[] = T_WHITESPACE;
		$prev = $phpcsFile->findPrevious($find, ($stackPtr - 1), null, true);
		
		$prefix = $stackPtr;
		while (($prefix = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$methodPrefixes, ($prefix - 1), $prev)) !== false) {
			switch ($tokens[$prefix]['code']) {
				case T_STATIC:
					$static = $prefix;
					break;
				case T_ABSTRACT:
					$abstract = $prefix;
					break;
				case T_FINAL:
					$final = $prefix;
					break;
				default:
					$visibility = $prefix;
					break;
			}
		}
		
		if ($abstract > $visibility) {
			$error = 'The abstract declaration must precede the visibility declaration';
			$phpcsFile->addError($error, $abstract, 'AbstractAfterVisibility');
		}
		
		if ($static !== 0 && $static < $visibility) {
			$error = 'The static declaration must come after the visibility declaration';
			$phpcsFile->addError($error, $static, 'StaticBeforeVisibility');
		}
		
		if ($final !== 0 && ($final < $visibility || $final < $static)) {
			$error = 'The final declaration must come after the visibility declaration and after the static declaration';
			$phpcsFile->addError($error, $final, 'FinalBeforeVisibilityOrBeforeStatic');
		}
	}
}