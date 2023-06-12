<?php

/**
 * Removes orphaned articles.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */

use wcf\data\article\ArticleAction;
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;

$sql = "SELECT  articleID
        FROM    wcf1_article
        WHERE   categoryID IS NULL";
$statement = WCF::getDB()->prepare($sql, 50);
$statement->execute();
$articleIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

if ($articleIDs === []) {
    return;
}

$articleAction = new ArticleAction($articleIDs, 'delete');
$articleAction->executeAction();

// Repeat the deletion if any article was deleted. If no deletable articles
// remain, the script will abort further above.
throw new SplitNodeException();
