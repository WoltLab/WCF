<?php
namespace wcf\data\poll\option;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the poll option object with functions to create, update and delete poll options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Poll\Option
 * 
 * @method static	PollOption	create(array $parameters = [])
 * @method		PollOption	getDecoratedObject()
 * @mixin		PollOption
 */
class PollOptionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PollOption::class;
}
