<?php
namespace wcf\data\comment\response;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Comment\Response
 * 
 * @method static	CommentResponse		create(array $parameters = [])
 * @method		CommentResponse		getDecoratedObject()
 * @mixin		CommentResponse
 */
class CommentResponseEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CommentResponse::class;
}
