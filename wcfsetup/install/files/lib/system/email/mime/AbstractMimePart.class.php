<?php
namespace wcf\system\email\mime;

/**
 * Represents a RFC 2045 / 2046 mime part of an email.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 * @since	2.2
 */
abstract class AbstractMimePart {
	/**
	 * Returns the Content-Type header value.
	 * 
	 * @return	string
	 */
	abstract public function getContentType();
	
	/**
	 * Returns the transfer encoding to use. Must either be
	 * 'quoted-printable' or 'base64'.
	 * 
	 * @return	string		either 'quoted-printable' or 'base64'
	 */
	abstract public function getContentTransferEncoding();
	
	/**
	 * Extra headers as an array of [ name, value] tuple for this mime part.
	 * As per RFC 2046 they may only start with X-* or Content-*. Content-Type
	 * and Content-Transfer-Encoding are blacklisted.
	 * 
	 * Returns an empty array by default.
	 *
	 * @return	array
	 */
	public function getAdditionalHeaders() {
		return [];
	}
	
	/**
	 * The body of this mime part.
	 * 
	 * @return	string
	 */
	abstract public function getContent();
}
