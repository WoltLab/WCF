<?php
namespace wcf\data\media;
use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Represents a list of madia files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Media
 * @since	3.0
 *
 * @method	Media		current()
 * @method	Media[]		getObjects()
 * @method	Media|null	search($objectID)
 * @property	Media[]		$objects
 */
class MediaList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = Media::class;
	
	/**
	 * Adds filters for the media files based on their file type.
	 * 
	 * @param	array		$filters
	 */
	public function addFileTypeFilters(array $filters) {
		if (isset($filters['isImage'])) {
			$this->getConditionBuilder()->add('isImage = ?', [$filters['isImage'] ? 1 : 0]);
		}
		
		if (isset($filters['fileTypes'])) {
			$conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
			foreach ($filters['fileTypes'] as $fileType) {
				if (substr($fileType, -1) == '*') {
					$conditionBuilder->add('fileType LIKE ?', [substr($fileType, 0, -1).'%']);
				}
				else {
					$conditionBuilder->add('fileType = ?', [$fileType]);
				}
			}
			
			$this->getConditionBuilder()->add($conditionBuilder->__toString(), $conditionBuilder->getParameters());
		}
	}
	
	/**
	 * Adds one of the default file filters.
	 * 
	 * Default filters are: 'image', 'pdf', 'text', 'other'.
	 * 
	 * @param	string		$filter
	 */
	public function addDefaultFileTypeFilter($filter) {
		switch ($filter) {
			case 'other':
				$this->getConditionBuilder()->add('media.fileType NOT LIKE ?', ['image/%']);
				$this->getConditionBuilder()->add('media.fileType <> ?', ['application/pdf']);
				$this->getConditionBuilder()->add('media.fileType NOT LIKE ?', ['text/%']);
			break;
			
			case 'image':
				$this->getConditionBuilder()->add('media.fileType LIKE ?', ['image/%']);
			break;
			
			case 'pdf':
				$this->getConditionBuilder()->add('media.fileType = ?', ['application/pdf']);
			break;
			
			case 'text':
				$this->getConditionBuilder()->add('media.fileType LIKE ?', ['text/%']);
			break;
		}
	}
	
	/**
	 * Adds conditions to search the media files by a certain search string.
	 * 
	 * @param	string		$searchString
	 */
	public function addSearchConditions($searchString) {
		$searchString = '%'.addcslashes($searchString, '_%').'%';
		
		$this->sqlConditionJoins .= ' LEFT JOIN wcf'.WCF_N.'_media_content media_content ON (media_content.mediaID = media.mediaID)';
		
		$conditionBuilder = new PreparedStatementConditionBuilder(false, 'OR');
		$conditionBuilder->add('media_content.title LIKE ?', [$searchString]);
		$conditionBuilder->add('media_content.caption LIKE ?', [$searchString]);
		$conditionBuilder->add('media_content.altText LIKE ?', [$searchString]);
		$conditionBuilder->add('media.filename LIKE ?', [$searchString]);
		$this->getConditionBuilder()->add($conditionBuilder->__toString(), $conditionBuilder->getParameters());
	}
}
