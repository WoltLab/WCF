<?php
namespace wcf\system\package;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\package\installation\queue\PackageInstallationQueueList;
use wcf\data\package\Package;
use wcf\system\exception\SystemException;
use wcf\system\Callback;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * Creates a logical node-based installation tree.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category	Community Framework
 */
class PackageInstallationNodeBuilder {
	/**
	 * active package installation dispatcher
	 * @var	wcf\system\package\PackageInstallationDispatcher
	 */
	public $installation = null;
	
	/**
	 * current installation node
	 * @var	string
	 */
	public $node = '';
	
	/**
	 * current parent installation node
	 * @var	string
	 */
	public $parentNode = '';
	
	/**
	 * list of requirements to be checked before package installation
	 * @var	array<array>
	 */
	public $requirements = array();
	
	/**
	 * current sequence number within one node
	 * @var	integer
	 */
	public $sequenceNo = 0;
	
	/**
	 * list of packages about to be installed
	 * @var	boolean
	 */
	protected static $pendingPackages = array();
	
	/**
	 * Creates a new instance of PackageInstallationNodeBuilder
	 * 
	 * @param	PackageInstallationDispatcher	$installation
	 */
	public function __construct(PackageInstallationDispatcher $installation) {
		$this->installation = $installation;
	}
	
	/**
	 * Sets parent node.
	 * 
	 * @param	string		$parentNode
	 */
	public function setParentNode($parentNode) {
		$this->parentNode = $parentNode;
	}
	
	/**
	 * Builds nodes for current installation queue.
	 */
	public function buildNodes() {
		// required packages
		$this->buildRequirementNodes();
		
		// install package itself
		$this->buildPackageNode();
		
		// package installation plugins
		$this->buildPluginNodes();
		
		// optional packages (ignored on update)
		if ($this->installation->queue->action == 'install') {
			$this->buildOptionalNodes();
		}
		
		// child queues
		$this->buildChildQueues();
	}
	
	/**
	 * Returns the succeeding node.
	 * 
	 * @param	string		$parentNode
	 * @return	string
	 */
	public function getNextNode($parentNode = '') {
		$sql = "SELECT	node
			FROM	wcf".WCF_N."_package_installation_node
			WHERE	processNo = ?
				AND parentNode = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->processNo,
			$parentNode
		));
		$row = $statement->fetchArray();
		
		if (!$row) {
			return '';
		}
		
		return $row['node'];
	}
	
	/**
	 * Returns package name associated with given queue id.
	 * 
	 * @param	integer		$queueID
	 * @return	string
	 */
	public function getPackageNameByQueue($queueID) {
		$sql = "SELECT	packageName
			FROM	wcf".WCF_N."_package_installation_queue
			WHERE	queueID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($queueID));
		$row = $statement->fetchArray();
		
		if (!$row) {
			return '';
		}
		
		return $row['packageName'];
	}
	
	/**
	 * Returns installation type by queue id.
	 * 
	 * @param	integer		$queueID
	 * @return	string
	 */
	public function getInstallationTypeByQueue($queueID) {
		$sql = "SELECT	action
			FROM	wcf".WCF_N."_package_installation_queue
			WHERE	queueID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($queueID));
		$row = $statement->fetchArray();
		
		return $row['action'];
	}
	
	/**
	 * Returns data for current node.
	 * 
	 * @param	string		$node
	 * @return	array
	 */
	public function getNodeData($node) {
		$sql = "SELECT		nodeType, nodeData, sequenceNo
			FROM		wcf".WCF_N."_package_installation_node
			WHERE		processNo = ?
					AND node = ?
			ORDER BY	sequenceNo ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->processNo,
			$node
		));
		$data = array();
		while ($row = $statement->fetchArray()) {
			$data[] = $row;
		}
		
		return $data;
	}
	
	/**
	 * Marks a node as completed.
	 * 
	 * @param	string		$node
	 */
	public function completeNode($node) {
		$sql = "UPDATE	wcf".WCF_N."_package_installation_node
			SET	done = 1
			WHERE	processNo = ?
				AND node = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->processNo,
			$node
		));
	}
	
	/**
	 * Removes all nodes associated with queue's process no.
	 * 
	 * CAUTION: This method SHOULD NOT be called within the installation process!
	 */
	public function purgeNodes() {
		$sql = "DELETE FROM	wcf".WCF_N."_package_installation_node
			WHERE		processNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->processNo
		));
		
		$sql = "DELETE FROM	wcf".WCF_N."_package_installation_form
			WHERE		queueID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->queueID
		));
	}
	
	/**
	 * Calculates current setup process.
	 * 
	 * @param	string		$node
	 * @return	integer
	 */
	public function calculateProgress($node) {
		$progress = array(
			'done' => 0,
			'outstanding' => 0
		);
		
		$sql = "SELECT	done
			FROM	wcf".WCF_N."_package_installation_node
			WHERE	processNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->processNo
		));
		while ($row = $statement->fetchArray()) {
			if ($row['done']) {
				$progress['done']++;
			}
			else {
				$progress['outstanding']++;
			}
		}
		
		if (!$progress['done']) {
			return 0;
		}
		else if (!$progress['outstanding']) {
			return 100;
		}
		else {
			$total = $progress['done'] + $progress['outstanding'];
			return round(($progress['done'] / $total) * 100);
		}
	}
	
	/**
	 * Duplicates a node by re-inserting it and moving all descendants into a new tree.
	 * 
	 * @param	string		$node
	 * @param	integer		$sequenceNo
	 */
	public function cloneNode($node, $sequenceNo) {
		$newNode = $this->getToken();
		
		// update descendants
		$sql = "UPDATE	wcf".WCF_N."_package_installation_node
			SET	parentNode = ?
			WHERE	parentNode = ?
				AND processNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$newNode,
			$node,
			$this->installation->queue->processNo
		));
		
		// create a copy of current node (prevents empty nodes)
		$sql = "SELECT	nodeType, nodeData, done
			FROM	wcf".WCF_N."_package_installation_node
			WHERE	node = ?
				AND processNo = ?
				AND sequenceNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$node,
			$this->installation->queue->processNo,
			$sequenceNo
		));
		$row = $statement->fetchArray();
		
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
					(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData, done)
			VALUES		(?, ?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->queueID,
			$this->installation->queue->processNo,
			0,
			$newNode,
			$node,
			$row['nodeType'],
			$row['nodeData'],
			$row['done']
		));
		
		// move other child-nodes greater than $sequenceNo into new node
		$sql = "UPDATE	wcf".WCF_N."_package_installation_node
			SET	parentNode = ?,
				node = ?,
				sequenceNo = (sequenceNo - ?)
			WHERE	node = ?
				AND processNo = ?
				AND sequenceNo > ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$node,
			$newNode,
			$sequenceNo,
			$node,
			$this->installation->queue->processNo,
			$sequenceNo
		));
	}
	
	/**
	 * Inserts a node before given target node. Will shift all target
	 * nodes to provide to be descendants of the new node. If you intend
	 * to insert more than a single node, you should prefer shiftNodes().
	 * 
	 * @param	string			$beforeNode
	 * @param	wcf\system\Callback	$callback
	 */
	public function insertNode($beforeNode, Callback $callback) {
		$newNode = $this->getToken();
		
		// update descendants
		$sql = "UPDATE	wcf".WCF_N."_package_installation_node
			SET	parentNode = ?
			WHERE	parentNode = ?
				AND processNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$newNode,
			$beforeNode,
			$this->installation->queue->processNo
		));
		
		// execute callback
		$callback($beforeNode, $newNode);
	}
	
	/**
	 * Shifts nodes to allow dynamic inserts at runtime.
	 * 
	 * @param	string		$oldParentNode
	 * @param	string		$newParentNode
	 */
	public function shiftNodes($oldParentNode, $newParentNode) {
		$sql = "UPDATE	wcf".WCF_N."_package_installation_node
			SET	parentNode = ?
			WHERE	parentNode = ?
				AND processNo = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$newParentNode,
			$oldParentNode,
			$this->installation->queue->processNo
		));
	}
	
	/**
	 * Builds package node used to install the package itself.
	 */
	protected function buildPackageNode() {
		if (!empty($this->node)) {
			$this->parentNode = $this->node;
			$this->sequenceNo = 0;
		}
		
		$this->node = $this->getToken();
					
		// calculate the number of instances of this package
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
					(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
			VALUES		(?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->queueID,
			$this->installation->queue->processNo,
			$this->sequenceNo,
			$this->node,
			$this->parentNode,
			'package',
			serialize(array(
				'package' => $this->installation->getArchive()->getPackageInfo('name'),
				'packageName' => $this->installation->getArchive()->getLocalizedPackageInfo('packageName'),
				'packageDescription' => $this->installation->getArchive()->getLocalizedPackageInfo('packageDescription'),
				'packageVersion' => $this->installation->getArchive()->getPackageInfo('version'),
				'packageDate' => $this->installation->getArchive()->getPackageInfo('date'),
				'packageURL' => $this->installation->getArchive()->getPackageInfo('packageURL'),
				'isApplication' => $this->installation->getArchive()->getPackageInfo('isApplication'),
				'author' => $this->installation->getArchive()->getAuthorInfo('author'),
				'authorURL' => $this->installation->getArchive()->getAuthorInfo('authorURL') !== null ? $this->installation->getArchive()->getAuthorInfo('authorURL') : '',
				'installDate' => TIME_NOW,
				'updateDate' => TIME_NOW,
				'requirements' => $this->requirements
			))
		));
		
		self::$pendingPackages[] = $this->installation->getArchive()->getPackageInfo('name');
	}
	
	/**
	 * Builds nodes for required packages, whereas each has it own node.
	 * 
	 * @return	string
	 */
	protected function buildRequirementNodes() {
		$queue = $this->installation->queue;
		
		// handle requirements
		$requiredPackages = $this->installation->getArchive()->getOpenRequirements();
		foreach ($requiredPackages as $packageName => $package) {
			if (!isset($package['file'])) {
				// requirements will be checked once package is about to be installed
				$this->requirements[$packageName] = array(
					'minVersion' => (isset($package['minversion'])) ? $package['minversion'] : '',
					'packageID' => $package['packageID']
				);
				
				continue;
			}
			
			if ($this->node == '' && !empty($this->parentNode)) {
				$this->node = $this->parentNode;
			}
			
			// extract package
			$index = $this->installation->getArchive()->getTar()->getIndexByFilename($package['file']);
			if ($index === false) {
				// workaround for WCFSetup
				if (!PACKAGE_ID && $packageName == 'com.woltlab.wcf') {
					continue;
				}
				
				throw new SystemException("Unable to find required package '".$package['file']."' within archive of package '".$this->installation->queue->package."'.");
			}
			
			$fileName = FileUtil::getTemporaryFilename('package_', preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', basename($package['file'])));
			$this->installation->getArchive()->getTar()->extract($index, $fileName);
			
			// get archive data
			$archive = new PackageArchive($fileName);
			$archive->openArchive();
			
			// get package id
			$sql = "SELECT	packageID
				FROM	wcf".WCF_N."_package
				WHERE	package = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($archive->getPackageInfo('name')));
			$row = $statement->fetchArray();
			$packageID = ($row === false) ? null : $row['packageID'];
			
			// create new queue
			$queue = PackageInstallationQueueEditor::create(array(
				'parentQueueID' => $queue->queueID,
				'processNo' => $queue->processNo,
				'userID' => WCF::getUser()->userID,
				'package' => $archive->getPackageInfo('name'),
				'packageID' => $packageID,
				'packageName' => $archive->getLocalizedPackageInfo('packageName'),
				'archive' => $fileName,
				'action' => ($packageID ? 'update' : 'install')
			));
			
			// spawn nodes
			$installation = new PackageInstallationDispatcher($queue);
			$installation->nodeBuilder->setParentNode($this->node);
			$installation->nodeBuilder->buildNodes();
			$this->node = $installation->nodeBuilder->getCurrentNode();
			
			self::$pendingPackages[] = $archive->getPackageInfo('name');
		}
	}
	
	/**
	 * Returns current node
	 * 
	 * @return	string
	 */
	public function getCurrentNode() {
		return $this->node;
	}
	
	/**
	 * Builds package installation plugin nodes, whereas pips could be grouped within
	 * one node, differ from each by nothing but the sequence number.
	 * 
	 * @return	string
	 */
	protected function buildPluginNodes() {
		$pluginNodes = array();
		
		$instructions = ($this->installation->getAction() == 'install') ? $this->installation->getArchive()->getInstallInstructions() : $this->installation->getArchive()->getUpdateInstructions();
		foreach ($instructions as $pip) {
			if (isset($pip['attributes']['run']) && ($pip['attributes']['run'] == 'standalone')) {
				// move into a new node
				$this->parentNode = $this->node;
				$this->node = $this->getToken();
				$this->sequenceNo = 0;
				
				$pluginNodes[] = array(
					'data' => $pip,
					'node' => $this->node,
					'parentNode' => $this->parentNode,
					'sequenceNo' => $this->sequenceNo
				);
				
				// create a new node for following PIPs
				$this->parentNode = $this->node;
				$this->node = $this->getToken();
				$this->sequenceNo = 0;
			}
			else {
				$this->sequenceNo++;
				
				$pluginNodes[] = array(
					'data' => $pip,
					'node' => $this->node,
					'parentNode' => $this->parentNode,
					'sequenceNo' => $this->sequenceNo
				);
			}
		}
		
		// insert nodes
		if (!empty($pluginNodes)) {
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
						(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
				VALUES		(?, ?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($pluginNodes as $index => $nodeData) {
				$statement->execute(array(
					$this->installation->queue->queueID,
					$this->installation->queue->processNo,
					$nodeData['sequenceNo'],
					$nodeData['node'],
					$nodeData['parentNode'],
					'pip',
					serialize($nodeData['data'])
				));
			}
		}
	}
	
	/**
	 * Builds nodes for optional packages, whereas each package exists within
	 * one node with the same parent node, seperated by sequence no (which does
	 * not really matter at this point).
	 */
	protected function buildOptionalNodes() {
		$packages = array();
		
		$optionalPackages = $this->installation->getArchive()->getOptionals();
		foreach ($optionalPackages as $package) {
			// check if already installed
			if (Package::isAlreadyInstalled($package['name'])) {
				continue;
			}
			
			// extract package
			$index = $this->installation->getArchive()->getTar()->getIndexByFilename($package['file']);
			if ($index === false) {
				throw new SystemException("Unable to find required package '".$package['file']."' within archive.");
			}
			
			$fileName = FileUtil::getTemporaryFilename('package_', preg_replace('!^.*(?=\.(?:tar\.gz|tgz|tar)$)!i', '', basename($package['file'])));
			$this->installation->getArchive()->getTar()->extract($index, $fileName);
			
			// get archive data
			$archive = new PackageArchive($fileName);
			$archive->openArchive();
			
			// check if all requirements are met
			$isInstallable = true;
			foreach ($archive->getOpenRequirements() as $packageName => $package) {
				if (!isset($package['file'])) {
					// requirement is neither installed nor shipped, check if it is about to be installed
					if (!in_array($packageName, self::$pendingPackages)) {
						$isInstallable = false;
						break;
					}
				}
			}
			
			$packages[] = array(
				'archive' => $fileName,
				'isInstallable' => $isInstallable,
				'package' => $archive->getPackageInfo('name'),
				'packageName' => $archive->getLocalizedPackageInfo('packageName'),
				'packageDescription' => $archive->getLocalizedPackageInfo('packageDescription'),
				'selected' => 0
			);
			
			self::$pendingPackages[] = $archive->getPackageInfo('name');
		}
		
		if (!empty($packages)) {
			$this->parentNode = $this->node;
			$this->node = $this->getToken();
			$this->sequenceNo = 0;
			
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
						(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
				VALUES		(?, ?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				$this->installation->queue->queueID,
				$this->installation->queue->processNo,
				$this->sequenceNo,
				$this->node,
				$this->parentNode,
				'optionalPackages',
				serialize($packages)
			));
		}
	}
	
	/**
	 * Recursively build nodes for child queues.
	 */
	protected function buildChildQueues() {
		$queueList = new PackageInstallationQueueList();
		$queueList->getConditionBuilder()->add("package_installation_queue.parentQueueID = ?", array($this->installation->queue->queueID));
		$queueList->getConditionBuilder()->add("package_installation_queue.queueID NOT IN (SELECT queueID FROM wcf".WCF_N."_package_installation_node)");
		$queueList->readObjects();
		
		foreach ($queueList as $queue) {
			$installation = new PackageInstallationDispatcher($queue);
			
			// work-around for iterative package updates
			if ($this->installation->queue->action == 'update' && $queue->package == $this->installation->queue->package) {
				$installation->setPreviousPackage(array(
					'package' => $this->installation->getArchive()->getPackageInfo('name'),
					'packageVersion' => $this->installation->getArchive()->getPackageInfo('version')
				));
			}
			
			$installation->nodeBuilder->setParentNode($this->node);
			$installation->nodeBuilder->buildNodes();
			$this->node = $installation->nodeBuilder->getCurrentNode();
		}
	}
	
	/**
	 * Returns a short SHA1-hash.
	 *
	 * @return	string
	 */
	protected function getToken() {
		return mb_substr(StringUtil::getRandomID(), 0, 8);
	}
	
	/**
	 * Returns queue id based upon current node.
	 * 
	 * @param	integer		$processNo
	 * @param	string		$node
	 * @return	integer
	 */
	public function getQueueByNode($processNo, $node) {
		$sql = "SELECT	queueID
			FROM	wcf".WCF_N."_package_installation_node
			WHERE	processNo = ?
				AND node = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$processNo,
			$node
		));
		$row = $statement->fetchArray();
		
		return $row['queueID'];
	}
}
