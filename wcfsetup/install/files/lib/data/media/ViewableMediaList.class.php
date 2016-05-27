<?php
namespace wcf\data\media;
use wcf\system\WCF;

/**
 * Represents a list of viewable madia files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.media
 * @category	Community Framework
 * @since	2.2
 *
 * @method	ViewableMedia		current()
 * @method	ViewableMedia[]		getObjects()
 * @method	ViewableMedia|null	search($objectID)
 * @property	ViewableMedia[]		$objects
 */
class ViewableMediaList extends MediaList {
	/**
	 * @inheritdoc
	 */
	public $decoratorClassName = ViewableMedia::class;
	
	/**
	 * @inheritdoc
	 */
	public function __construct() {
		parent::__construct();
		
		// fetch content data
		$this->sqlSelects .= "media_content.*";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_media_content media_content ON (media_content.mediaID = media.mediaID AND media_content.languageID = COALESCE(media.languageID, ".WCF::getLanguage()->languageID."))";
	}
}
