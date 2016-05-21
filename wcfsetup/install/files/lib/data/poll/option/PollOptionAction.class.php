<?php
namespace wcf\data\poll\option;
use wcf\data\AbstractDatabaseObjectAction;

/**
 * Executes poll option-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.poll.option
 * @category	Community Framework
 */
class PollOptionAction extends AbstractDatabaseObjectAction {
	/**
	 * @inheritDoc
	 */
	protected $className = PollOptionEditor::class;
}
