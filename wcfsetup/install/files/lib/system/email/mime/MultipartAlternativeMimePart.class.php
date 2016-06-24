<?php
namespace wcf\system\email\mime;
use wcf\util\StringUtil;

/**
 * Represents a multipart/alternative mime container.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Email\Mime
 * @since	3.0
 */
class MultipartAlternativeMimePart extends AbstractMultipartMimePart {
	/**
	 * @inheritDoc
	 */
	public function getContentType() {
		return "multipart/alternative;\r\n   boundary=\"".$this->boundary."\"";
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getConcatenatedParts($parts) {
		$sortedParts = new \SplPriorityQueue();
		
		$parts->rewind();
		while ($parts->valid()) {
			$sortedParts->insert($parts->current(), PHP_INT_MAX - $parts->getInfo());
			$parts->next();
		}
		
		return parent::getConcatenatedParts($sortedParts);
	}
	
	/**
	 * Adds a mime part to this multipart container.
	 * 
	 * The given priority determines the ordering within the Email. A higher priority
	 * mime part will be further down the email (see RFC 2046, 5.1.4).
	 * 
	 * @param	AbstractMimePart	$part
	 * @param	integer			$data	The priority.
	 * @throws	\InvalidArgumentException
	 * @throws	\DomainException
	 */
	// @codingStandardsIgnoreStart
	public function addMimePart(AbstractMimePart $part, $data = 1000) {
		parent::addMimePart($part, $data);
	}
	// @codingStandardsIgnoreEnd
}
