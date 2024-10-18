<?php

namespace wcf\system\box\command;

use wcf\data\box\Box;
use wcf\data\page\Page;
use wcf\system\WCF;

/**
 * Assigns pages to a certain box.
 *
 * Note: The primary use of this command is to be used during package installation.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @property-read array<string> $pageIdentifiers
 */
final class CreateBoxToPageAssignments
{
    public function __construct(
        private readonly string $boxIdentifier,
        private readonly array $pageIdentifiers,
        private readonly bool $visible = true,
    ) {
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function __invoke()
    {
        $box = Box::getBoxByIdentifier($this->boxIdentifier);
        if ($box === null) {
            throw new \InvalidArgumentException("Unknown box with identifier '{$this->boxIdentifier}'");
        }

        $pages = [];
        foreach ($this->pageIdentifiers as $pageIdentifier) {
            $page = Page::getPageByIdentifier($pageIdentifier);
            if ($page === null) {
                throw new \InvalidArgumentException("Unknown page with identifier '{$pageIdentifier}'");
            }
            $pages[] = $page;
        }

        if (($this->visible && $box->visibleEverywhere) || (!$this->visible && !$box->visibleEverywhere)) {
            $sql = "DELETE FROM     wcf1_box_to_page
                    WHERE           boxID = ?
                            AND pageID = ?";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($pages as $page) {
                $statement->execute([$box->boxID, $page->pageID]);
            }
        } else {
            $sql = "REPLACE INTO    wcf1_box_to_page
                                    (boxID, pageID, visible)
                    VALUES          (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);
            foreach ($pages as $page) {
                $statement->execute([$box->boxID, $page->pageID, $this->visible ? 1 : 0]);
            }
        }
    }
}
