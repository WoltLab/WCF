<?php
namespace wcf\system\email;

/**
 * Holds RFC 2045 and RFC 5322 grammar tokens and provides helper functions
 * for dealing with these RFCs.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email
 * @since	3.0
 */
final class EmailGrammar {
	/**
	 * Returns a regular expression matching the given type in RFC 5322.
	 * 
	 * @param	string	$type
	 * @return	string
	 */
	public static function getGrammar($type) {
		switch ($type) {
			case 'VCHAR':
				return '[\x21-\x7E]';
			case 'WSP':
				return '[\x20\x09]';
			case 'FWS':
				return "(?:(?:".self::getGrammar('WSP')."*\r\n)?".self::getGrammar('WSP')."+)";
			case 'CFWS':
				// note: no support for comments
				return self::getGrammar('FWS');
			case 'quoted-pair':
				return "(?:\\\\(?:".self::getGrammar('WSP')."|".self::getGrammar('VCHAR')."))";
			case 'atext':
				return "[a-zA-Z0-9!#$%&'*+-/=?^_`{|}~]";
			case 'atom':
				return "(?:".self::getGrammar('CFWS')."?".self::getGrammar('atext')."+".self::getGrammar('CFWS')."?)";
			case 'id-left':
			case 'list-label':
			case 'dot-atom-text':
				return "(?:".self::getGrammar('atext')."+(?:\\.".self::getGrammar('atext').'+)*)';
			case 'no-fold-literal':
				return "(?:\\[".self::getGrammar('dtext')."*\\])";
			case 'id-right':
				return "(?:".self::getGrammar('dot-atom-text')."|".self::getGrammar('no-fold-literal').")";
			case 'dot-atom':
				return "(?:".self::getGrammar('CFWS')."?".self::getGrammar('dot-atom-text').self::getGrammar('CFWS')."?)";
			case 'qtext':
				return '[\x21\x23-\x5B\x5D-\x7E]';
			case 'qcontent':
				return "(?:".self::getGrammar('qtext')."|".self::getGrammar('quoted-pair').")";
			case 'quoted-string':
				return "(?:".self::getGrammar('CFWS')."?\"(?:".self::getGrammar('FWS')."?".self::getGrammar('qcontent').")*".self::getGrammar('FWS')."?\"".self::getGrammar('CFWS')."?)";
			case 'word':
				return "(?:".self::getGrammar('atom')."|".self::getGrammar('quoted-string').")";
			case 'display-name':
			case 'phrase':
				return "(?:".self::getGrammar('word')."+)";
			case 'local-part':
				return "(?:".self::getGrammar('dot-atom')."|".self::getGrammar('quoted-string').")";
			case 'dtext':
				return '[\x21-\x5A\x5E-\x7E]';
			case 'domain-literal':
				return "(?:".self::getGrammar('CFWS')."?\\[(?:".self::getGrammar('FWS')."?".self::getGrammar('dtext').")*".self::getGrammar('FWS')."?\\]".self::getGrammar('CFWS')."?)";
			case 'domain':
				return "(?:".self::getGrammar('dot-atom')."|".self::getGrammar('domain-literal').")";
			case 'addr-spec':
				return "(?:".self::getGrammar('local-part')."@".self::getGrammar('domain').")";
			case 'angle-addr':
				return "(?:".self::getGrammar('CFWS')."?<".self::getGrammar('addr-spec').">".self::getGrammar('CFWS')."?)";
			case 'name-addr':
				return "(?:".self::getGrammar('display-name')."?".self::getGrammar('angle-addr').")";
			case 'mailbox':
				return "(?:".self::getGrammar('name-addr')."|".self::getGrammar('addr-spec').")";
			case 'msg-id':
				return "(?:".self::getGrammar('CFWS')."?<".self::getGrammar('id-left')."@".self::getGrammar('id-right').">".self::getGrammar('CFWS')."?)";
		}
	}
	
	/**
	 * Encode text using quoted printable encoding.
	 * 
	 * @param	string	$header		Header to encode
	 * @return	string			Encoded header
	 */
	public static function encodeQuotedPrintableHeader($header) {
		return mb_encode_mimeheader($header, "UTF-8", "Q", "\r\n");
	}
	
	/**
	 * Return text either unmodified, if it matches the 'atom' grammar,
	 * in double quotes or encoded in quoted printable. Depending on which
	 * encoding is necessary.
	 *
	 * @param	string	$header		Header to encode
	 * @return	string			Encoded header
	 */
	public static function encodeHeader($header) {
		if (!preg_match('(^'.self::getGrammar('atom').'$)', $header)) {
			if (($encoded = self::encodeQuotedPrintableHeader($header)) === $header) {
				$header = '"'.addcslashes($header, '\\"').'"';
			}
			else {
				$header = $encoded;
			}
		}
		
		return $header;
	}
	
	/**
	 * Forbid creation of EmailGrammar objects.
	 */
	private function __construct() {
		// does nothing
	}
}
