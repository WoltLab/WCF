<?php

/**
 * This script removes duplicate background jobs.
 */

use wcf\system\WCF;

$sql = "DELETE background_jobs
        FROM   wcf1_background_job background_jobs
        JOIN   (
            SELECT   MIN(jobID) as keepID, identifier
            FROM     wcf1_background_job
            WHERE    identifier IS NOT NULL
            GROUP BY identifier
            HAVING   COUNT(*) > 1
        ) AS duplicates
            ON background_jobs.identifier = duplicates.identifier
        WHERE  background_jobs.jobID <> duplicates.keepID";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
