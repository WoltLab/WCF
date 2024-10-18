<?php

namespace wcf\acp\form;

use wcf\data\tag\Tag;
use wcf\data\tag\TagAction;
use wcf\data\tag\TagEditor;
use wcf\data\tag\TagList;
use wcf\form\AbstractForm;
use wcf\system\exception\IllegalLinkException;
use wcf\system\WCF;

/**
 * Shows the tag edit form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TagEditForm extends TagAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.tag.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.tag.canManageTag'];

    /**
     * tag id
     * @var int
     */
    public $tagID = 0;

    /**
     * tag object
     * @var Tag
     */
    public $tagObj;

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['id'])) {
            $this->tagID = \intval($_REQUEST['id']);
        }
        $this->tagObj = new Tag($this->tagID);
        if (!$this->tagObj->tagID) {
            throw new IllegalLinkException();
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        AbstractForm::save();

        // update tag
        $this->objectAction = new TagAction([$this->tagID], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'name' => $this->name,
            ]),
        ]);
        $this->objectAction->executeAction();

        if ($this->tagObj->synonymFor === null) {
            // remove synonyms first
            $sql = "UPDATE  wcf1_tag
                    SET     synonymFor = ?
                    WHERE   synonymFor = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([
                null,
                $this->tagID,
            ]);

            $editor = new TagEditor($this->tagObj);
            foreach ($this->synonyms as $synonym) {
                if (empty($synonym)) {
                    continue;
                }

                // find existing tag
                $synonymObj = Tag::getTag($synonym, $this->tagObj->languageID);
                if ($synonymObj === null) {
                    $synonymAction = new TagAction([], 'create', [
                        'data' => [
                            'name' => $synonym,
                            'languageID' => $this->tagObj->languageID,
                            'synonymFor' => $this->tagID,
                        ],
                    ]);
                    $synonymAction->executeAction();
                } else {
                    $editor->addSynonym($synonymObj);
                }
            }
        }

        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        $this->languageID = $this->tagObj->languageID;

        parent::readData();

        if (empty($_POST)) {
            $this->name = $this->tagObj->name;
            $this->languageID = $this->tagObj->languageID;
        }

        $synonymList = new TagList();
        $synonymList->getConditionBuilder()->add('synonymFor = ?', [$this->tagObj->tagID]);
        $synonymList->readObjects();
        $this->synonyms = [];
        foreach ($synonymList as $synonym) {
            $this->synonyms[] = $synonym->name;
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'tagObj' => $this->tagObj,
            'action' => 'edit',
            'synonym' => ($this->tagObj !== null && $this->tagObj->synonymFor) ? new Tag($this->tagObj->synonymFor) : null,
        ]);
    }
}
