<?php
namespace wcf\data\media;
use wcf\system\WCF;

/**
 * Represents a list of viewable media files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Media
 * @since	3.0
 *
 * @method	ViewableMedia		current()
 * @method	ViewableMedia[]		getObjects()
 * @method	ViewableMedia|null	search($objectID)
 * @property	ViewableMedia[]		$objects
 */
class ViewableMediaList extends MediaList {
	/**
	 * @inheritDoc
	 */
	public $decoratorClassName = ViewableMedia::class;
	
	/**
	 * Creates a new ViewableMediaList object.
	 *
	 * @param       int|null        $languageID
	 */
	public function __construct($languageID = null) {
		parent::__construct();
		
		if ($languageID === null) {
			$languageID = WCF::getLanguage()->languageID;
		}
		
		// fetch content data
		$this->sqlSelects .= "media_content.*, COALESCE(media.languageID, ".$languageID.") AS localizedLanguageID";
		$this->sqlJoins .= " LEFT JOIN wcf".WCF_N."_media_content media_content ON (media_content.mediaID = media.mediaID AND media_content.languageID = COALESCE(media.languageID, ".$languageID."))";
	}
}
