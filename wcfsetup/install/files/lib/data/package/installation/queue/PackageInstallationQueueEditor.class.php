<?php
namespace wcf\data\package\installation\queue;
use wcf\data\DatabaseObjectEditor;

/**
 * Provides functions to edit package installation queues.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Installation\Queue
 * 
 * @method	PackageInstallationQueue	getDecoratedObject()
 * @mixin	PackageInstallationQueue
 */
class PackageInstallationQueueEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = PackageInstallationQueue::class;
}
