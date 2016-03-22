<?php
namespace wcf\system\email\mime;
use wcf\system\email\UserMailbox;
use wcf\system\WCF;

/**
 * HtmlTextMimePart is a text/html implementation of a RecipientAwareTextMimePart.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 * @since	2.2
 */
class HtmlTextMimePart extends RecipientAwareTextMimePart {
	/**
	 * Creates a new HtmlTextMimePart.
	 * 
	 * @param	string	$content	Content of this text part.
	 */
	public function __construct($content) {
		parent::__construct($content, 'text/html', 'email');
	}
}
