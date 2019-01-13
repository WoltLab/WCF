<?php
namespace wcf\system\user\content\provider;
use wcf\data\comment\response\CommentResponse;

/**
 * User content provider for comment responses.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Content\Provider
 * @since	5.2
 */
class CommentResponseUserContentProvider extends AbstractDatabaseUserContentProvider {
	/**
	 * @inheritdoc
	 */
	public static function getDatabaseObjectClass() {
		return CommentResponse::class;
	}
}
