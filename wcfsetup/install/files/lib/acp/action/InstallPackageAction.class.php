<?php
namespace wcf\acp\action;
use wcf\action\AbstractDialogAction;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\exception\IllegalLinkException;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles an AJAX-based package installation.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class InstallPackageAction extends AbstractDialogAction {
	/**
	 * current node
	 *
	 * @var	string
	 */
	public $node = '';
	
	/**
	 * PackageInstallationDispatcher object
	 *
	 * @var	PackageInstallationDispatcher
	 */
	public $installation = null;
	
	/**
	 * PackageInstallationQueue object
	 *
	 * @var	PackageInstallationQueue
	 */
	public $queue = null;
	
	/**
	 * current queue id
	 *
	 * @var	integer
	 */
	public $queueID = 0;
	
	// system
	public $templateName = 'packageInstallationStep';
	
	/**
	 * @see wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['node'])) $this->node = StringUtil::trim($_POST['node']);
		if (isset($_POST['queueID'])) $this->queueID = intval($_POST['queueID']);
		$this->queue = new PackageInstallationQueue($this->queueID);
		
		if (!$this->queue->queueID) {
			throw new IllegalLinkException();
		}
		
		$this->installation = new PackageInstallationDispatcher($this->queue);
	}
	
	/**
	 * Executes installation based upon nodes.
	 */
	protected function stepInstall() {
		$step = $this->installation->install($this->node);
		
		if ($step->hasDocument()) {
			$this->data = array(
				'innerTemplate' => $step->getTemplate(),
				'node' => $step->getNode(),
				'progress' => $this->installation->nodeBuilder->calculateProgress($this->node),
				'step' => 'install'
			);
		}
		else {
			if ($step->getNode() == '') {
				// perform final actions
				$queueID = $this->installation->completeSetup();
				
				// begin with next queue
				if ($queueID) {
					$this->data = array(
						'progress' => 100,
						'queueID' => $queueID,
						'step' => 'prepare'
					);
					return;
				}
				
				// no more queues, show success
				$this->data = array(
					'progress' => 100,
					'step' => 'success'
				);
				return;
			}
			
			// continue with next node
			$this->data = array(
				'step' => 'install',
				'node' => $step->getNode(),
				'progress' => $this->installation->nodeBuilder->calculateProgress($this->node)
			);
		}
	}
	
	/**
	 * Prepares the installation process.
	 */
	protected function stepPrepare() {
		// update package information
		$this->installation->updatePackage();
		
		// clean-up previously created nodes
		$this->installation->nodeBuilder->purgeNodes();
		
		// create node tree
		$this->installation->nodeBuilder->buildNodes();
		$nextNode = $this->installation->nodeBuilder->getNextNode();
		
		WCF::getTPL()->assign(array(
			'queue' => $this->queue
		));
		
		$this->data = array(
			'template' => WCF::getTPL()->fetch($this->templateName),
			'step' => 'install',
			'node' => $nextNode,
			'currentAction' => WCF::getLanguage()->get('wcf.package.installation.step.installing'),
			'progress' => 0
		);
	}
	
	/**
	 * @see	wcf\action\AbstractDialogAction\AbstractDialogAction::validateStep()
	 */
	protected function validateStep() {
		switch ($this->step) {
			case 'install':
			case 'prepare':
				continue;
			break;
			
			default:
				throw new IllegalLinkException();
			break;
		}
	}
}
