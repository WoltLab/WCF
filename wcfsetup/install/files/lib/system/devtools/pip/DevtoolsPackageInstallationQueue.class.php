<?php
namespace wcf\system\devtools\pip;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\WCF;

/**
 * Specialized implementation to emulate a regular package installation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Devtools\Pip
 * @since       3.1
 */
class DevtoolsPackageInstallationQueue extends PackageInstallationQueue {
	/**
	 * @inheritDoc
	 */
	public function __construct(DevtoolsProject $project) {
		parent::__construct(null, [
			'queueID' => 0,
			'parentQueueID' => 0,
			'processNo' => 0,
			'userID' => WCF::getUser()->userID,
			'package' => $project->getPackage()->package,
			'packageName' => $project->getPackage()->getName(),
			'archive' => '',
			'action' => 'update',
			'done' => 0,
			'isApplication' => $project->getPackage()->isApplication
		]);
	}
}
