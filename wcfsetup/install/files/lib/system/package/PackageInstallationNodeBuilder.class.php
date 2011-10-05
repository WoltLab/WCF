<?php
namespace wcf\system\package;
use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

/**
 * PackageInstallationNodeBuilder creates a logical node-based installation tree.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
class PackageInstallationNodeBuilder {
	/**
	 * instance of PackageInstallationDispatcher
	 *
	 * @var	PackageInstallationDispatcher
	 */
	public $installation = null;
	
	/**
	 * current node
	 *
	 * @var	string
	 */
	public $node = '';
	
	/**
	 * current parent node
	 *
	 * @var	string
	 */
	public $parentNode = '';
	
	/**
	 * current sequence number within one node
	 *
	 * @var	integer
	 */
	public $sequenceNo = 0;
	
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
		
		// optional packages
		$this->buildOptionalNodes();
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
		else if ($progress['done'] == $progress['outstanding']) {
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
	public function insertNode($node, $sequenceNo) {
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
	 * Builds package node used to install the package itself.
	 */
	protected function buildPackageNode() {
		if (!empty($this->node)) {
			$this->parentNode = $this->node;
			$this->sequenceNo = 0;
		}
		
		$this->node = $this->getToken();
					
		// calculate the number of instances of this package
		$instanceNo = 1;
		$sql = "SELECT	COUNT(*) AS count, MAX(instanceNo) AS instanceNo
			FROM	wcf".WCF_N."_package
			WHERE	package = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->getArchive()->getPackageInfo('name')
		));
		$row = $statement->fetchArray();
		
		if ($row['count'] > 0) $instanceNo = $row['instanceNo'] + 1;
		
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
				'packageName' => $this->installation->getArchive()->getPackageInfo('packageName'),
				'instanceNo' => $instanceNo,
				'packageDescription' => $this->installation->getArchive()->getPackageInfo('packageDescription'),
				'packageVersion' => $this->installation->getArchive()->getPackageInfo('version'),
				'packageDate' => $this->installation->getArchive()->getPackageInfo('date'),
				'packageURL' => $this->installation->getArchive()->getPackageInfo('packageURL'),
				'standalone' => $this->installation->getArchive()->getPackageInfo('standalone'),
				'author' => $this->installation->getArchive()->getAuthorInfo('author'),
				'authorURL' => $this->installation->getArchive()->getAuthorInfo('authorURL') !== null ? $this->installation->getArchive()->getAuthorInfo('authorURL') : '',
				'installDate' => TIME_NOW,
				'updateDate' => TIME_NOW
			))
		));
	}
	
	/**
	 * Builds nodes for required packages, whereas each has it own node.
	 *
	 * @return	string
	 */
	protected function buildRequirementNodes() {
		$packageNodes = array();
		$queue = $this->installation->queue;
		
		$requiredPackages = $this->installation->getArchive()->getRequirements();
		foreach ($requiredPackages as $packageName => $package) {
			if (!isset($package['file'])) {
				// ignore requirements which are not to be installed
				continue;
			}
			
			if ($this->node == '' && !empty($this->parentNode)) {
				$this->node = $this->parentNode;
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
			
			// create new queue
			$queue = PackageInstallationQueueEditor::create(array(
				'parentQueueID' => $queue->queueID,
				'processNo' => $queue->processNo,
				'userID' => WCF::getUser()->userID,
				'package' => $archive->getPackageInfo('name'),
				'packageName' => $archive->getPackageInfo('packageName'),
				'archive' => $fileName,
				'action' => $queue->action
			));
			
			// spawn nodes
			$installation = new PackageInstallationDispatcher($queue);
			$installation->nodeBuilder->setParentNode($this->node);
			$installation->nodeBuilder->buildNodes();
			$this->node = $installation->nodeBuilder->getCurrentNode();
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
				$this->parentNode = $this->node;
				$this->node = $this->getToken();
				$this->sequenceNo = 0;
				
				$pluginNodes[] = array(
					'data' => $pip,
					'node' => $this->node,
					'parentNode' => $this->parentNode,
					'sequenceNo' => $this->sequenceNo
				);
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
		if (count($pluginNodes) > 0) {
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
		$packageNodes = array();
		
		$optionalPackages = $this->installation->getArchive()->getOptionals();
		foreach ($optionalPackages as $package) {
			$packageNodes[] = array(
				'data' => $package
			);
			
			$lastNode = $newNode;
		}
		
		if (!empty($packageNodes)) {
			$this->parentNode = $this->node;
			$this->node = $this->getToken();
			$this->sequenceNo = -1;
			
			$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
						(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
				VALUES		(?, ?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			foreach ($packageNodes as $nodeData) {
				$this->sequenceNo = 0;
				
				$statement->execute(array(
					$this->installation->queue->queueID,
					$this->installation->queue->processNo,
					$this->sequenceNo,
					$this->node,
					$this->parentNode,
					'optionalPackage',
					serialize($nodeData['data'])
				));
			}
		}
	}
	
	/**
	 * Returns a short SHA1-hash.
	 *
	 * @return	string
	 */
	protected function getToken() {
		return StringUtil::substring(StringUtil::getRandomID(), 0, 8);
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
