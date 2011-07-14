<?php
namespace wcf\system\package;
use wcf\system\WCF;

/**
 * PackageUninstallationNodeBuilder creates a logical node-based uninstallation tree.
 *
 * @todo	Change to use Prepared Statements, see line 42
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
		// build pip nodes
		$this->buildPluginNodes();
		
		// remove package
		$this->buildPackageNode();
	}
	
	/**
	 * Creates a node-tree for package installation plugins, whereas the PIP- and files-plugin
	 * will be executed last.
	 */
	protected function buildPluginNodes() {
		$this->node = $this->getToken();
		
		// fetch ordered pips
		$pips = array();
		$sql = "SELECT		pluginName, className,
					CASE pluginName WHEN 'packageinstallationplugins' THEN 1 WHEN 'files' THEN 2 ELSE 0 END 'pluginOrder'
			FROM		wcf".WCF_N."_package_installation_plugin
			WHERE		packageID IN (
						1 /* TESTING ONLY */
						/*
						SELECT	dependency
						FROM	wcf".WCF_N."_package_dependency
						WHERE	packageID = ".$this->installation->queue->packageID."
						*/
					)
			ORDER BY	pluginOrder ASC, priority DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$pips[] = $row;
		}
		
		// insert pips
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
					(queueID, processNo, sequenceNo, node, nodeType, nodeData)
			VALUES		(?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$sequenceNo = 0;
		
		foreach ($pips as $pip) {
			$statement->execute(array(
				$this->installation->queue->queueID,
				$this->installation->queue->processNo,
				$sequenceNo,
				$this->node,
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
?>
