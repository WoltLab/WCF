<?php
/**
 * Discourages use of raw PCRE functions. \wcf\system\Regex is a superior way to use Regex.
 * 
 * @author	Tim Duesterhus
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
class WCF_Sniffs_PHP_DiscouragePCRESniff/* extends Generic_Sniffs_PHP_ForbiddenFunctionsSniff*/ {
	/**
	 * A list of forbidden functions with their alternatives.
	 *
	 * The value is NULL if no alternative exists. IE, the
	 * function should just not be used.
	 *
	 * @var array(string => string|null)
	 */
	protected $forbiddenFunctions = array(
		'preg_match_all' => null,
		'preg_match' => null,
		'preg_replace' => null,
		'preg_replace_callback' => null,
		'preg_split' => null
	);
	
	/**
	 * Constructor.
	 */
	public function __construct() {
		return;
	}
	
	/**
	 * Generates the error or warning for this sniff.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param int	 	 		$stackPtr  The position of the forbidden function
	 *	 	 	 	 	 	 	 	 in the token array.
	 * @param string	 	 	 $function  The name of the forbidden function.
	 * @param string	 	 	 $pattern   The pattern used for the match.
	 *
	 * @return void
	 */
	protected function addError($phpcsFile, $stackPtr, $function, $pattern = null) {
		$data = array($function);
		$error = 'Use of raw PCRE %s() is discouraged. Please use \wcf\system\Regex which provides OO-access to regex';
		$type = 'Discourage';
		
		$phpcsFile->addWarning($error, $stackPtr, $type, $data);
	}
}
