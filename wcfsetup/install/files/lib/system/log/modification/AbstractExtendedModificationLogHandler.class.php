<?php
namespace wcf\system\log\modification;

/**
 * Abstract implementation of a modification log handler that can provide readable outputs
 * for the global modification log in the ACP.
 *
 * @author      Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\System\Log\Modification
 * @since       5.2
 */
abstract class AbstractExtendedModificationLogHandler extends AbstractModificationLogHandler implements IExtendedModificationLogHandler {
	/**
	 * @inheritDoc
	 */
	public function includeInLogList() {
		return true;
	}
}
