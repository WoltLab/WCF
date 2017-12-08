<?php
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Adds database columns, each row in the data section
 * below is executed in a separate request.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2017 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */
$data = <<<DATA
ALTER TABLE wcf1_user ADD COLUMN coverPhotoHash CHAR(40) DEFAULT NULL, ADD COLUMN coverPhotoExtension VARCHAR(4) NOT NULL DEFAULT '', ADD COLUMN disableCoverPhoto TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN disableCoverPhotoReason TEXT, ADD COLUMN disableCoverPhotoExpires INT(10) NOT NULL DEFAULT 0;
DATA;

$lines = explode("\n", StringUtil::trim($data));

$rebuildData = WCF::getSession()->getVar('__wcfUpdateAddColumns');
if ($rebuildData === null) {
	$rebuildData = [
		'i' => 0,
		'max' => count($lines)
	];
}

// MySQL adds a column by creating a new table in the
// background and copying over all the data afterwards.
// 
// Using a single `ALTER TABLE` to add multiple columns
// results in the same runtime, because copying the table
// is what actually takes ages.
$statement = WCF::getDB()->prepareStatement(str_replace('wcf1_', 'wcf'.WCF_N.'_', $lines[$rebuildData['i']]));
$statement->execute();

$rebuildData['i']++;

if ($rebuildData['i'] === $rebuildData['max']) {
	WCF::getSession()->unregister('__wcfUpdateAddColumns');
}
else {
	WCF::getSession()->register('__wcfUpdateAddColumns', $rebuildData);
	
	// call this script again
	throw new SplitNodeException();
}
