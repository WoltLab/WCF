<?php
namespace wcf\system\feed\enclosure;

/**
 * Represents an enclosure in a rss feed.
 *
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Feed\Enclosure
 */
class FeedEnclosure {
	/**
	 * url to the enclosure
	 * @var string
	 */
	protected $url = '';
	
	/**
	 * enclosure's MIME type
	 * @var string
	 */
	protected $type = '';
	
	/**
	 * size of the enclosure in bytes
	 * @var integer
	 */
	protected $length = 0;
	
	/**
	 * Creates a new FeedEnclosure object.
	 *
	 * @param       string          $url            url to the enclosure
	 * @param       string          $type           enclosure's MIME type
	 * @param       integer         $length         size of the enclosure in bytes
	 */
	public function __construct($url, $type, $length) {
		$this->url = $url;
		$this->type = $type;
		$this->length = $length;
	}
	
	/**
	 * Returns the url to the enclosure.
	 * 
	 * @return string
	 */
	public function getURL() {
		return $this->url;
	}
	
	/**
	 * Returns the enclosure's MIME type.
	 * 
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Returns the size of the enclosure in bytes.
	 * 
	 * @return integer
	 */
	public function getLength() {
		return $this->length;
	}
}
