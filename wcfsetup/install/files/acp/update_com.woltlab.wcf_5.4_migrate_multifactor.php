<?php

/**
 * Migrates multifactor authentication data from the Two Step Verification plugin developed
 * by Hanashi Development.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use ParagonIE\ConstantTime\Base32;
use ParagonIE\ConstantTime\Hex;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\PackageCache;
use wcf\data\user\User;
use wcf\data\user\UserEditor;
use wcf\system\user\authentication\password\algorithm\Wcf1;
use wcf\system\user\authentication\password\PasswordAlgorithmManager;
use wcf\system\user\multifactor\Setup;
use wcf\system\WCF;

$hanashiTwoStep = PackageCache::getInstance()->getPackageByIdentifier('eu.hanashi.wsc.two-step-verification');

if (!$hanashiTwoStep) {
    return;
}

// Fetch the object types for the relevant MFA methods.
$totpMethod = ObjectTypeCache::getInstance()
    ->getObjectTypeByName('com.woltlab.wcf.multifactor', 'com.woltlab.wcf.multifactor.totp');
$backupMethod = ObjectTypeCache::getInstance()
    ->getObjectTypeByName('com.woltlab.wcf.multifactor', 'com.woltlab.wcf.multifactor.backup');

// Fetch the backup code hashing algorithm.
// We use the Wcf1 algorithm as it's super cheap compared to BCrypt and the previous
// backup codes were stored in plaintext, leading to a net improvement.
$hashAlgorithm = new Wcf1();
$hashAlgorithmName = PasswordAlgorithmManager::getInstance()->getNameFromAlgorithm($hashAlgorithm);

// Fetch the affected user IDs.
$sql = "SELECT	DISTINCT userID
	FROM	wcf" . WCF_N . "_user_authenticator
	WHERE		type = ?
		AND	userID NOT IN (
			SELECT	userID
			FROM 	wcf" . WCF_N . "_user_multifactor
			WHERE	objectTypeID = ?
		)";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
    'totp',
    $totpMethod->objectTypeID,
]);
$userIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

// Prepare the statements for use in user processing.
$sql = "SELECT	name, secret, time
	FROM	wcf" . WCF_N . "_user_authenticator
	WHERE		type = ?
		AND	userID = ?
	FOR UPDATE";
$existingTotpAuthenticatorStatement = WCF::getDB()->prepareStatement($sql);
$sql = "SELECT	backupCode
	FROM	wcf" . WCF_N . "_user_backup_code
	WHERE	userID = ?
	FOR UPDATE";
$existingBackupStatement = WCF::getDB()->prepareStatement($sql);

$sql = "INSERT INTO	wcf" . WCF_N . "_user_multifactor_totp
			(setupID, deviceID, deviceName, secret, minCounter, createTime)
	VALUES		(?, ?, ?, ?, ?, ?)";
$createTotpStatement = WCF::getDB()->prepareStatement($sql);
$sql = "INSERT INTO	wcf" . WCF_N . "_user_multifactor_backup
			(setupID, identifier, code, createTime)
	VALUES		(?, ?, ?, ?)";
$createBackupStatement = WCF::getDB()->prepareStatement($sql);

// TODO: Do we need to split this across multiple requests?
foreach ($userIDs as $userID) {
    WCF::getDB()->beginTransaction();

    // Do not use UserRuntimeCache due to possible memory constraints.
    $user = new User($userID);
    $userEditor = new UserEditor($user);

    if (Setup::find($totpMethod, $user) !== null) {
        // Skip this user, because they have an enabled TOTP method.
        // This should never happen, because these users are filtered out
        // when selecting, but we are going to play safe.
        continue;
    }

    $totpSetup = Setup::allocateSetUpId($totpMethod, $user);

    $existingTotpAuthenticatorStatement->execute([
        'totp',
        $user->userID,
    ]);
    $earliestTotp = null;
    while ($row = $existingTotpAuthenticatorStatement->fetchArray()) {
        $createTotpStatement->execute([
            $totpSetup->getId(),
            Hex::encode(\random_bytes(16)),
            $row['name'],
            Base32::decodeUpper($row['secret']),
            ($row['time'] / 30),
            $row['time'],
        ]);

        if ($earliestTotp === null || $earliestTotp > $row['time']) {
            $earliestTotp = $row['time'];
        }
    }

    $backupSetup = Setup::allocateSetUpId($backupMethod, $user);
    $existingBackupStatement->execute([
        $user->userID,
    ]);
    $usedIdentifiers = [];
    while ($row = $existingBackupStatement->fetchArray()) {
        // We intentionally do not validate the signature for resiliency and because
        // we trust the database to not contain bogus information.
        $parts = \explode('-', $row['backupCode'], 2);
        if (\count($parts) < 2) {
            continue;
        }

        $code = @\base64_decode($parts[1]);
        if (!$code) {
            continue;
        }

        $identifier = \mb_substr($code, 0, 5, '8bit');

        if (isset($usedIdentifiers[$identifier])) {
            continue;
        }
        $usedIdentifiers[$identifier] = $identifier;

        $createBackupStatement->execute([
            $backupSetup->getId(),
            $identifier,
            $hashAlgorithmName . ':' . $hashAlgorithm->hash($code),
            $earliestTotp,
        ]);
    }

    $userEditor->update([
        'multifactorActive' => 1,
    ]);

    WCF::getDB()->commitTransaction();
}
