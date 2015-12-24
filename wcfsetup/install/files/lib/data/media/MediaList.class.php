<?php
namespace wcf\data\media;
use wcf\data\DatabaseObjectList;
use wcf\system\database\util\PreparedStatementConditionBuilder;

/**
 * Represents a list of madia files.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.media
 * @category	Community Framework
 * @since	2.2
 */
class MediaList extends DatabaseObjectList {
	/**
	 * @inheritdoc
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
}
