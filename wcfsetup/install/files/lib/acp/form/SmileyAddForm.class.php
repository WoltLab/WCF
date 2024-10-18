<?php

namespace wcf\acp\form;

use wcf\data\category\Category;
use wcf\data\category\CategoryNodeTree;
use wcf\data\smiley\SmileyAction;
use wcf\data\smiley\SmileyEditor;
use wcf\form\AbstractForm;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\UserInputException;
use wcf\system\language\I18nHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\ImageUtil;
use wcf\util\StringUtil;

/**
 * Shows the smiley add form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SmileyAddForm extends AbstractForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.smiley.add';

    /**
     * @inheritDoc
     */
    public $templateName = 'smileyAdd';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.smiley.canManageSmiley'];

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_SMILEY'];

    /**
     * primary smiley code
     * @var string
     */
    public $smileyCode = '';

    /**
     * show order value
     * @var int
     */
    public $showOrder = 0;

    /**
     * categoryID value
     * @var int
     */
    public $categoryID = 0;

    /**
     * smileyTitle
     * @var string
     */
    public $smileyTitle = '';

    /**
     * aliases value
     * @var string
     */
    public $aliases = '';

    /**
     * path to the smiley file
     * @var string
     */
    public $smileyPath = '';

    /**
     * path to the smiley file (2x)
     * @var string
     */
    public $smileyPath2x = '';

    /**
     * node tree with available smiley categories
     * @var CategoryNodeTree
     */
    public $categoryNodeTree;

    /**
     * data of the uploaded smiley file
     * @var array
     */
    public $fileUpload = [];

    /**
     * data of the uploaded smiley file (2x)
     * @var array
     */
    public $fileUpload2x = [];

    /**
     * temporary name of the uploaded smiley file
     * @var string
     */
    public $uploadedFilename = '';

    /**
     * temporary name of the uploaded smiley file (2x)
     * @var string
     */
    public $uploadedFilename2x = '';

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables();

        WCF::getTPL()->assign([
            'action' => 'add',
            'smileyTitle' => $this->smileyTitle,
            'showOrder' => $this->showOrder,
            'categoryID' => $this->categoryID,
            'smileyCode' => $this->smileyCode,
            'aliases' => $this->aliases,
            'smileyPath' => $this->smileyPath,
            'smileyPath2x' => $this->smileyPath2x,
            'categoryNodeList' => $this->categoryNodeTree->getIterator(),
            'uploadedFilename' => $this->uploadedFilename,
            'uploadedFilename2x' => $this->uploadedFilename2x,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        $this->categoryNodeTree = new CategoryNodeTree('com.woltlab.wcf.bbcode.smiley', 0, true);
    }

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        I18nHandler::getInstance()->register('smileyTitle');
    }

    /**
     * @inheritDoc
     */
    public function readFormParameters()
    {
        parent::readFormParameters();

        I18nHandler::getInstance()->readValues();

        if (I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
            $this->smileyTitle = I18nHandler::getInstance()->getValue('smileyTitle');
        }

        if (isset($_POST['showOrder'])) {
            $this->showOrder = \intval($_POST['showOrder']);
        }
        if (isset($_POST['categoryID'])) {
            $this->categoryID = \intval($_POST['categoryID']);
        }
        if (isset($_POST['smileyCode'])) {
            $this->smileyCode = StringUtil::trim($_POST['smileyCode']);
        }
        if (isset($_POST['aliases'])) {
            $this->aliases = StringUtil::unifyNewlines(StringUtil::trim($_POST['aliases']));
        }
        if (isset($_POST['smileyPath'])) {
            $this->smileyPath = FileUtil::removeLeadingSlash(StringUtil::trim($_POST['smileyPath']));
        }
        if (isset($_POST['uploadedFilename'])) {
            $this->uploadedFilename = StringUtil::trim($_POST['uploadedFilename']);
        }
        if (isset($_FILES['fileUpload'])) {
            $this->fileUpload = $_FILES['fileUpload'];
        }
        if (isset($_POST['smileyPath2x'])) {
            $this->smileyPath2x = FileUtil::removeLeadingSlash(StringUtil::trim($_POST['smileyPath2x']));
        }
        if (isset($_POST['uploadedFilename2x'])) {
            $this->uploadedFilename2x = StringUtil::trim($_POST['uploadedFilename2x']);
        }
        if (isset($_FILES['fileUpload2x'])) {
            $this->fileUpload2x = $_FILES['fileUpload2x'];
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        parent::save();

        $this->objectAction = new SmileyAction([], 'create', [
            'data' => \array_merge($this->additionalFields, [
                'smileyTitle' => $this->smileyTitle,
                'smileyCode' => $this->smileyCode,
                'aliases' => $this->aliases,
                'smileyPath' => $this->smileyPath,
                'smileyPath2x' => $this->smileyPath2x,
                'showOrder' => $this->showOrder,
                'categoryID' => $this->categoryID ?: null,
                'packageID' => 1,
            ]),
            'fileLocation' => $this->uploadedFilename ? WCF_DIR . 'images/smilies/' . $this->uploadedFilename : '',
            'fileLocation2x' => $this->uploadedFilename2x ? WCF_DIR . 'images/smilies/' . $this->uploadedFilename2x : '',
        ]);
        $this->objectAction->executeAction();
        $returnValues = $this->objectAction->getReturnValues();
        $smileyEditor = new SmileyEditor($returnValues['returnValues']);
        $smileyID = $returnValues['returnValues']->smileyID;

        if (!I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
            I18nHandler::getInstance()->save('smileyTitle', 'wcf.smiley.title' . $smileyID, 'wcf.smiley', 1);

            // update title
            $smileyEditor->update([
                'smileyTitle' => 'wcf.smiley.title' . $smileyID,
            ]);
        }

        // reset values
        $this->smileyCode = '';
        $this->categoryID = 0;
        $this->showOrder = 0;
        $this->smileyPath = $this->smileyPath2x = '';
        $this->aliases = '';
        $this->uploadedFilename = $this->uploadedFilename2x = '';

        I18nHandler::getInstance()->reset();

        $this->saved();

        // show success message
        WCF::getTPL()->assign([
            'success' => true,
            'objectEditLink' => LinkHandler::getInstance()->getControllerLink(
                SmileyEditForm::class,
                ['id' => $smileyID]
            ),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if ($this->uploadedFilename) {
            if (!\file_exists(WCF_DIR . 'images/smilies/' . $this->uploadedFilename)) {
                $this->uploadedFilename = '';
                throw new UserInputException('fileUpload', 'uploadFailed');
            }
        } elseif (!empty($this->fileUpload['name'])) {
            if (!ImageUtil::isImage($this->fileUpload['tmp_name'], $this->fileUpload['name'])) {
                $this->uploadedFilename = '';
                throw new UserInputException('fileUpload', 'noImage');
            }

            do {
                $this->uploadedFilename = StringUtil::getRandomID() . '.' . \mb_strtolower(\mb_substr(
                    $this->fileUpload['name'],
                    \mb_strrpos($this->fileUpload['name'], '.') + 1
                ));
            } while (\file_exists(WCF_DIR . 'images/smilies/' . $this->uploadedFilename));

            if (
                !@\move_uploaded_file(
                    $this->fileUpload['tmp_name'],
                    WCF_DIR . 'images/smilies/' . $this->uploadedFilename
                )
            ) {
                $this->uploadedFilename = '';
                throw new UserInputException('fileUpload', 'uploadFailed');
            }
        } else {
            if (empty($this->smileyPath)) {
                throw new UserInputException('smileyPath');
            }

            if (!\is_file(WCF_DIR . $this->smileyPath)) {
                throw new UserInputException('smileyPath', 'notFound');
            }
        }

        if ($this->uploadedFilename2x) {
            if (!\file_exists(WCF_DIR . 'images/smilies/' . $this->uploadedFilename2x)) {
                $this->uploadedFilename2x = '';
                throw new UserInputException('fileUpload2x', 'uploadFailed');
            }
        } elseif (!empty($this->fileUpload2x['name'])) {
            if (!ImageUtil::isImage($this->fileUpload2x['tmp_name'], $this->fileUpload2x['name'])) {
                $this->uploadedFilename2x = '';
                throw new UserInputException('fileUpload2x', 'noImage');
            }

            do {
                $this->uploadedFilename2x = StringUtil::getRandomID() . '.' . \mb_strtolower(\mb_substr(
                    $this->fileUpload2x['name'],
                    \mb_strrpos($this->fileUpload2x['name'], '.') + 1
                ));
            } while (\file_exists(WCF_DIR . 'images/smilies/' . $this->uploadedFilename2x));

            if (
                !@\move_uploaded_file(
                    $this->fileUpload2x['tmp_name'],
                    WCF_DIR . 'images/smilies/' . $this->uploadedFilename2x
                )
            ) {
                $this->uploadedFilename2x = '';
                throw new UserInputException('fileUpload2x', 'uploadFailed');
            }
        } elseif ($this->smileyPath2x && !\is_file(WCF_DIR . $this->smileyPath2x)) {
            throw new UserInputException('smileyPath2x', 'notFound');
        }

        // validate title
        if (!I18nHandler::getInstance()->validateValue('smileyTitle')) {
            if (I18nHandler::getInstance()->isPlainValue('smileyTitle')) {
                throw new UserInputException('smileyTitle');
            } else {
                throw new UserInputException('smileyTitle', 'multilingual');
            }
        }

        if ($this->categoryID) {
            $category = new Category($this->categoryID);
            if (!$category->categoryID) {
                throw new UserInputException('categoryID', 'invalid');
            }
        }

        if (empty($this->smileyCode)) {
            throw new UserInputException('smileyCode');
        }

        // validate smiley code and aliases against existing smilies
        $conditionBuilder = new PreparedStatementConditionBuilder();
        if (isset($this->smiley)) {
            $conditionBuilder->add('smileyID <> ?', [$this->smiley->smileyID]);
        }
        $sql = "SELECT  smileyCode, aliases
                FROM    wcf1_smiley
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $aliases = \explode("\n", $this->aliases);
        $aliases = \array_map('\mb_strtolower', $aliases);
        while ($row = $statement->fetchArray()) {
            $known = [];
            if (!empty($row['aliases'])) {
                $known = \explode("\n", $row['aliases']);
            }
            $known[] = $row['smileyCode'];
            $known = \array_map('\mb_strtolower', $known);

            if (\in_array(\mb_strtolower($this->smileyCode), $known)) {
                throw new UserInputException('smileyCode', 'notUnique');
            } else {
                $conflicts = \array_intersect($aliases, $known);
                if (!empty($conflicts)) {
                    throw new UserInputException('aliases', 'notUnique');
                }
            }
        }
    }
}
