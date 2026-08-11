<?php

namespace wcf\system\package;

use wcf\data\package\installation\queue\PackageInstallationQueue;
use wcf\system\form\FormDocument;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles form documents associated with a queue.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class PackageInstallationFormManager
{
    /**
     * Handles a POST or GET request.
     *
     * @param PackageInstallationQueue $queue
     * @return void
     */
    public static function handleRequest(PackageInstallationQueue $queue)
    {
        $formName = isset($_REQUEST['formName']) ? StringUtil::trim($_REQUEST['formName']) : '';

        // ignore request
        if (empty($formName) || !self::findForm($queue, $formName)) {
            return;
        }

        // get document
        $document = self::getForm($queue, $formName);
        $document->handleRequest();

        self::updateForm($queue, $document);
    }

    /**
     * Registers a form document.
     *
     * @return void
     */
    public static function registerForm(PackageInstallationQueue $queue, FormDocument $document)
    {
        if (self::findForm($queue, $document->getName())) {
            self::updateForm($queue, $document);
        } else {
            self::insertForm($queue, $document);
        }
    }

    /**
     * Searches for an existing form document associated with given queue.
     *
     * @return  bool
     */
    public static function findForm(PackageInstallationQueue $queue, string $formName)
    {
        $sql = "SELECT  COUNT(*)
                FROM    wcf1_package_installation_form
                WHERE   queueID = ?
                    AND formName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $queue->queueID,
            $formName,
        ]);

        return $statement->fetchSingleColumn() > 0;
    }

    /**
     * Inserts a form document into database.
     */
    private static function insertForm(PackageInstallationQueue $queue, FormDocument $document): void
    {
        $sql = "INSERT INTO wcf1_package_installation_form
                            (queueID, formName, document)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $queue->queueID,
            $document->getName(),
            \base64_encode(\serialize($document)),
        ]);
    }

    /**
     * Updates a form document database entry.
     */
    private static function updateForm(PackageInstallationQueue $queue, FormDocument $document): void
    {
        $sql = "UPDATE  wcf1_package_installation_form
                SET     document = ?
                WHERE   queueID = ?
                    AND formName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            \base64_encode(\serialize($document)),
            $queue->queueID,
            $document->getName(),
        ]);
    }

    /**
     * Deletes form documents associated with given queue.
     *
     * @param PackageInstallationQueue $queue
     * @return void
     */
    public static function deleteForms(PackageInstallationQueue $queue)
    {
        $sql = "DELETE FROM wcf1_package_installation_form
                WHERE       queueID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$queue->queueID]);
    }

    /**
     * Returns a form document from database.
     *
     * @return ?FormDocument
     */
    public static function getForm(PackageInstallationQueue $queue, string $formName)
    {
        $sql = "SELECT  document
                FROM    wcf1_package_installation_form
                WHERE   queueID = ?
                    AND formName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $queue->queueID,
            $formName,
        ]);
        $row = $statement->fetchArray();

        if ($row !== false) {
            $document = \base64_decode($row['document'], true);
            if ($document === false) {
                return null;
            }

            return \unserialize($document);
        }

        return null;
    }
}
