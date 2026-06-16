<?php

namespace wcf\data\tag;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\ISearchAction;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;

/**
 * Executes tagging-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Tag, TagEditor>
 */
class TagAction extends AbstractDatabaseObjectAction implements ISearchAction
{
    /**
     * @inheritDoc
     */
    protected $allowGuestAccess = ['getSearchResultList'];

    /**
     * @inheritDoc
     */
    protected $className = TagEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.tag.canManageTag'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.tag.canManageTag'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['delete', 'update'];

    #[\Override]
    public function validateGetSearchResultList()
    {
        $this->readString('searchString', false, 'data');
        $this->readInteger('languageID', true);

        if (isset($this->parameters['data']['excludedSearchValues']) && !\is_array($this->parameters['data']['excludedSearchValues'])) {
            throw new UserInputException('excludedSearchValues');
        }
    }

    #[\Override]
    public function create()
    {
        $tag = parent::create();

        $editor = new TagEditor($tag);
        $this->saveSynonyms($editor);

        return $tag;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->objects as $tagEditor) {
            if ($tagEditor->synonymFor !== null) {
                continue;
            }

            // remove synonyms first
            $sql = "UPDATE  wcf1_tag
                    SET     synonymFor = ?
                    WHERE   synonymFor = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                null,
                $tagEditor->tagID,
            ]);

            $this->saveSynonyms($tagEditor);
        }
    }

    private function saveSynonyms(TagEditor $tagEditor): void
    {
        $synonyms = $this->parameters['synonyms'] ?? [];
        foreach ($synonyms as $synonym) {
            if (empty($synonym)) {
                continue;
            }

            // find existing tag
            $synonymObj = Tag::getTag($synonym, $tagEditor->languageID);
            if ($synonymObj === null) {
                $synonymAction = new TagAction([], 'create', [
                    'data' => [
                        'name' => $synonym,
                        'languageID' => $tagEditor->languageID,
                        'synonymFor' => $tagEditor->tagID,
                    ],
                ]);
                $synonymAction->executeAction();
            } else {
                $tagEditor->addSynonym($synonymObj);
            }
        }
    }

    #[\Override]
    public function getSearchResultList()
    {
        $excludedSearchValues = [];
        if (isset($this->parameters['data']['excludedSearchValues'])) {
            $excludedSearchValues = $this->parameters['data']['excludedSearchValues'];
        }
        $list = [];

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add("name LIKE ?", [WCF::getDB()->escapeLikeValue($this->parameters['data']['searchString']) . '%']);
        if (!empty($excludedSearchValues)) {
            $conditionBuilder->add("name NOT IN (?)", [$excludedSearchValues]);
        }

        if ($this->parameters['languageID']) {
            $conditionBuilder->add("languageID = ?", [$this->parameters['languageID']]);
        }

        // find tags
        $sql = "SELECT  tagID, name
                FROM    wcf1_tag
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql, 5);
        $statement->execute($conditionBuilder->getParameters());
        while ($row = $statement->fetchArray()) {
            $list[] = [
                'label' => $row['name'],
                'objectID' => $row['tagID'],
            ];
        }

        return $list;
    }
}
