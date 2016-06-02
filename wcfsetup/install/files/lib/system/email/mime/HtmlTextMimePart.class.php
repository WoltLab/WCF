<?php
namespace wcf\system\email\mime;

/**
 * HtmlTextMimePart is a text/html implementation of a RecipientAwareTextMimePart.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Mime
 * @since	3.0
 */
class HtmlTextMimePart extends RecipientAwareTextMimePart {
	/**
	 * Creates a new HtmlTextMimePart.
	 * 
	 * @param	string	$content	Content of this text part.
	 */
	public function __construct($content) {
		parent::__construct('text/html', 'email_html', 'wcf', $content);
	}
}
