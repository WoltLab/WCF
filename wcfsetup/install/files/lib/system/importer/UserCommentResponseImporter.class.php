<?php
namespace wcf\system\importer;

/**
 * Imports user profile comment response.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.importer
 * @category	Community Framework
 */
class UserCommentResponseImporter extends AbstractCommentResponseImporter {
	/**
	 * @see	\wcf\system\importer\AbstractCommentResponseImporter::$objectTypeName
	 */
	protected $objectTypeName = 'com.woltlab.wcf.user.comment';
}
