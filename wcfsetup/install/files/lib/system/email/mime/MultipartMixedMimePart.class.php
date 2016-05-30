<?php
namespace wcf\system\email\mime;
use wcf\util\StringUtil;

/**
 * Represents a multipart/mixed mime container.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.email.mime
 * @category	Community Framework
 * @since	2.2
 */
class MultipartMixedMimePart extends AbstractMultipartMimePart {
	/**
	 * @inheritDoc
	 */
	public function getContentType() {
		return "multipart/mixed;\r\n   boundary=\"".$this->boundary."\"";
	}
	
	/**
	 * Adds a mime part to this multipart container.
	 * 
	 * The given $data is ignored.
	 * 
	 * @param	AbstractMimePart	$part
	 * @param	mixed			$data	Ignored.
	 * @throws	\InvalidArgumentException
	 * @throws	\DomainException
	 */
	public function addMimePart(AbstractMimePart $part, $data = null) {
		return parent::addMimePart($part, $data);
	}
}
