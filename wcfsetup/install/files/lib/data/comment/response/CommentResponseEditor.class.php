<?php
namespace wcf\data\comment\response;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit comment responses.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 * 
 * @method	CommentResponse		getDecoratedObject()
 * @mixin	CommentResponse
 */
class CommentResponseEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = CommentResponse::class;
}
