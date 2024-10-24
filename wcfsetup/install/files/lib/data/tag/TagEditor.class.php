<?php

namespace wcf\data\tag;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit tags.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method static Tag create(array $parameters = [])
 * @method      Tag getDecoratedObject()
 * @mixin       Tag
 */
class TagEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Tag::class;

    /**
     * Adds the given tag, and all of it's synonyms as a synonym.
     *
     * @param Tag $synonym
     */
    public function addSynonym(Tag $synonym)
    {
        if ($synonym->tagID == $this->tagID) {
            return;
        }

        // assign all associations for the synonym with this tag
        $sql = "UPDATE IGNORE   wcf1_tag_to_object
                SET             tagID = ?
                WHERE           tagID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->tagID, $synonym->tagID]);

        // remove remaining associations (object was tagged with both tags => duplicate key previously ignored)
        $sql = "DELETE FROM wcf1_tag_to_object
                WHERE       tagID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$synonym->tagID]);

        $editor = new self($synonym);
        $editor->update(['synonymFor' => $this->tagID]);

        $synonymList = new TagList();
        $synonymList->getConditionBuilder()->add('synonymFor = ?', [$synonym->tagID]);
        $synonymList->readObjects();

        foreach ($synonymList as $synonym) {
            $this->addSynonym($synonym);
        }
    }
}
