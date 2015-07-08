<?php
namespace wcf\system\email\mime;
use wcf\system\email\UserMailbox;
use wcf\system\WCF;

/**
 * PlainTextMimePart is a text/plain implementation of an
 * AbstractRecipientAwareTextMimePart.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 */
class PlainTextMimePart extends AbstractRecipientAwareTextMimePart {
	/**
	 * template to use for this email
	 * @var	string
	 */
	protected $template = 'emailTextPlain';
	
	/**
	 * Creates a new PlainTextMimePart.
	 * 
	 * @param	string	$content	Content of this text part.
	 */
	public function __construct($content) {
		parent::__construct($content, 'text/plain');
	}
}
