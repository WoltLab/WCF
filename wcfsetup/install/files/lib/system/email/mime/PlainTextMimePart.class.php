<?php
namespace wcf\system\email\mime;

/**
 * PlainTextMimePart is a text/plain implementation of a RecipientAwareTextMimePart.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 * @since	2.2
 */
class PlainTextMimePart extends RecipientAwareTextMimePart {
	/**
	 * Creates a new PlainTextMimePart.
	 * 
	 * @param	string	$content	Content of this text part.
	 */
	public function __construct($content) {
		parent::__construct($content, 'text/plain', 'email');
	}
}
