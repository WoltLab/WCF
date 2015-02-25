<?php
namespace wcf\system\attachment;

/**
 * Any attachment object type should implement this interface.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.attachment
 * @category	Community Framework
 */
interface IAttachmentObjectType {
	/**
	 * Returns true if the active user has the permission to download attachments.
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function canDownload($objectID);
	
	/**
	 * Returns true if the active user has the permission to view attachment
	 * previews (thumbnails).
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function canViewPreview($objectID);
	
	/**
	 * Returns true if the active user has the permission to upload attachments.
	 * 
	 * @param	integer		$objectID
	 * @param	integer		$parentObjectID
	 * @return	boolean
	 */
	public function canUpload($objectID, $parentObjectID = 0);
	
	/**
	 * Returns true if the active user has the permission to delete attachments.
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function canDelete($objectID);
	
	/**
	 * Returns the maximum filesize for an attachment.
	 * 
	 * @return	integer
	 */
	public function getMaxSize();
	
	/**
	 * Returns the allowed file extensions.
	 * 
	 * @return	array<string>
	 */
	public function getAllowedExtensions();
	
	/**
	 * Returns the maximum number of attachments.
	 * 
	 * @return	integer
	 */
	public function getMaxCount();
	
	/**
	 * Gets the container object of an attachment.
	 * 
	 * @param	integer		$objectID
	 * @return	\wcf\data\IUserContent
	 */
	public function getObject($objectID);
	
	/**
	 * Caches the data of the given container objects.
	 * 
	 * @param	array<integer>		$objectIDs
	 */
	public function cacheObjects(array $objectIDs);
	
	/**
	 * Loads the permissions for given attachments.
	 * 
	 * @param	array<\wcf\data\attachment\Attachment>		$attachments
	 */
	public function setPermissions(array $attachments);
}
