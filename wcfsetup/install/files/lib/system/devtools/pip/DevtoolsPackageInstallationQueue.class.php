<?php
namespace wcf\system\devtools\pip;
use wcf\data\devtools\project\DevtoolsProject;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\WCF;

class DevtoolsPackageInstallationQueue extends PackageInstallationQueue {
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