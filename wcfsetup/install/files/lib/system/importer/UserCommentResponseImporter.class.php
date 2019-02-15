<?php
namespace wcf\system\importer;

/**
 * Imports user profile comment response.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
class UserCommentResponseImporter extends AbstractCommentResponseImporter {
	/**
	 * @inheritDoc
	 */
	protected $objectTypeName = 'com.woltlab.wcf.user.comment';
}
