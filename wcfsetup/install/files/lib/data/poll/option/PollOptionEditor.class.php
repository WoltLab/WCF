<?php
namespace wcf\data\poll\option;
use wcf\data\DatabaseObjectEditor;

/**
 * Extends the poll option object with functions to create, update and delete poll options.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.poll.option
 * @category	Community Framework
 * 
 * @method	PollOption	getDecoratedObject()
 * @mixin	PollOption
 */
class PollOptionEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PollOption::class;
}
