<?php
namespace wcf\system\package;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\system\package\PackageUninstallationDispatcher;
use wcf\system\WCF;

/**
 * PackageUninstallationNodeBuilder creates a logical node-based uninstallation tree.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package
 * @category 	Community Framework
 */
class PackageUninstallationNodeBuilder extends PackageInstallationNodeBuilder {
	/**
	 * Builds node for current uninstallation queue.
	 */
	public function buildNodes() {
		if (!empty($this->parentNode)) {
			$this->node = $this->getToken();
		}
		
		// build nodes for dependent packages
		$this->buildDependentPackageNodes();
		
		// build pip nodes
		$this->buildPluginNodes();
		
		// remove package
		$this->buildPackageNode();
	}
	
	/**
	 * Builds nodes for all dependent packages.
	 */
	protected function buildDependentPackageNodes() {
		if (!PackageUninstallationDispatcher::hasDependencies($this->installation->queue->packageID)) {
			return;
		}
		
		$packageList = PackageUninstallationDispatcher::getOrderedPackageDependencies($this->installation->queue->packageID);
		$queue = $this->installation->queue;
		
		foreach ($packageList as $package) {
			$queue = PackageInstallationQueueEditor::create(array(
				'processNo' => $queue->processNo,
				'parentQueueID' => $queue->queueID,
				'userID' => WCF::getUser()->userID,
				'package' => $package->package,
				'packageName' => $package->getName(),
				'packageID' => $package->packageID,
				'action' => 'uninstall'
			));
			
			// spawn nodes
			$uninstallation = new PackageUninstallationDispatcher($queue);
			$uninstallation->nodeBuilder->setParentNode($this->node);
			$uninstallation->nodeBuilder->buildNodes();
			$this->parentNode = $uninstallation->nodeBuilder->getCurrentNode();
			$this->node = $this->getToken();
		}
	}
	
	/**
	 * Creates a node-tree for package installation plugins, whereas the PIP- and files-plugin
	 * will be executed last.
	 */
	protected function buildPluginNodes() {
		if (empty($this->node)) {
			$this->node = $this->getToken();
		}
		
		// fetch ordered pips
		$pips = array();
		$sql = "SELECT		pluginName, className,
					CASE pluginName WHEN 'packageinstallationplugin' THEN 1 WHEN 'files' THEN 2 ELSE 0 END AS pluginOrder
			FROM		wcf".WCF_N."_package_installation_plugin
			ORDER BY	pluginOrder, priority";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$pips[] = $row;
		}
		
		// insert pips
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
					(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
			VALUES		(?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$sequenceNo = 0;
		
		foreach ($pips as $pip) {
			$statement->execute(array(
				$this->installation->queue->queueID,
				$this->installation->queue->processNo,
				$sequenceNo,
				$this->node,
				$this->parentNode,
				'pip',
				serialize(array(
					'pluginName' => $pip['pluginName'],
					'className' => $pip['className']
				))
			));
			
			$sequenceNo++;
		}
	}
	
	/**
	 * Builds node for package removal
	 */
	protected function buildPackageNode() {
		$this->parentNode = $this->node;
		$this->node = $this->getToken();
		
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
					(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
			VALUES		(?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$this->installation->queue->queueID,
			$this->installation->queue->processNo,
			0,
			$this->node,
			$this->parentNode,
			'package',
			'a:0:{}'
		));
	}
}
