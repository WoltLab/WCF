<?php
namespace wcf\system\package;
use wcf\system\WCF;

/**
 * Creates a logical node-based uninstallation tree.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package
 */
class PackageUninstallationNodeBuilder extends PackageInstallationNodeBuilder {
	/**
	 * @inheritDoc
	 */
	public function buildNodes() {
		if (!empty($this->parentNode)) {
			$this->node = $this->getToken();
		}
		
		// build pip nodes
		$this->buildPluginNodes();
		
		// remove package
		$this->buildPackageNode();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function buildPluginNodes() {
		if (empty($this->node)) {
			$this->node = $this->getToken();
		}
		
		// fetch ordered pips
		$pips = [];
		$sql = "SELECT		pluginName, className,
					CASE pluginName WHEN 'packageinstallationplugin' THEN 1 WHEN 'file' THEN 2 ELSE 0 END AS pluginOrder
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
			$statement->execute([
				$this->installation->queue->queueID,
				$this->installation->queue->processNo,
				$sequenceNo,
				$this->node,
				$this->parentNode,
				'pip',
				serialize([
					'pluginName' => $pip['pluginName'],
					'className' => $pip['className']
				])
			]);
			
			$sequenceNo++;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function buildPackageNode() {
		$this->parentNode = $this->node;
		$this->node = $this->getToken();
		
		$sql = "INSERT INTO	wcf".WCF_N."_package_installation_node
					(queueID, processNo, sequenceNo, node, parentNode, nodeType, nodeData)
			VALUES		(?, ?, ?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$this->installation->queue->queueID,
			$this->installation->queue->processNo,
			0,
			$this->node,
			$this->parentNode,
			'package',
			'a:0:{}'
		]);
	}
}
