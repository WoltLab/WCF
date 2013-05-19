<?php
namespace wcf\acp;
use wcf\data\package\Package;
use wcf\data\package\PackageList;
use wcf\system\WCF;

/**
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
$packageList = new PackageList();
$packageList->getConditionBuilder()->add("isApplication = 1");
$packageList->readObjects();

$sql = "UPDATE	wcf".WCF_N."_acp_template
	SET	application = ?
	WHERE	packageID = ?";
$acpStatement = WCF::getDB()->prepareStatement($sql);

$sql = "UPDATE	wcf".WCF_N."_package_installation_file_log
	SET	application = ?
	WHERE	packageID = ?";
$logStatement = WCF::getDB()->prepareStatement($sql);

$sql = "UPDATE	wcf".WCF_N."_template
	SET	application = ?
	WHERE	packageID = ?";
$tplStatement = WCF::getDB()->prepareStatement($sql);

foreach ($packageList as $package) {
	$acpStatement->execute(array(
		Package::getAbbreviation($package->package),
		$package->packageID
	));
	
	$logStatement->execute(array(
		Package::getAbbreviation($package->package),
		$package->packageID
	));
	
	$tplStatement->execute(array(
		Package::getAbbreviation($package->package),
		$package->packageID
	));
}

// assign all other files/templates to WCF
$sql = "UPDATE	wcf".WCF_N."_acp_template
	SET	application = ?
	WHERE	application = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array('wcf', ''));

$sql = "UPDATE	wcf".WCF_N."_package_installation_file_log
	SET	application = ?
	WHERE	application = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array('wcf', ''));

$sql = "UPDATE	wcf".WCF_N."_template
	SET	application = ?
	WHERE	application = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute(array('wcf', ''));
