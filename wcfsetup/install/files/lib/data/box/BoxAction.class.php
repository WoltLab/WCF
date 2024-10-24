<?php

namespace wcf\data\box;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\data\box\content\BoxContent;
use wcf\data\box\content\BoxContentEditor;
use wcf\data\IToggleAction;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\TDatabaseObjectToggle;
use wcf\system\box\IConditionBoxController;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\html\simple\HtmlSimpleParser;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;
use wcf\system\version\VersionTracker;
use wcf\system\WCF;

/**
 * Executes box related actions.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method  BoxEditor[] getObjects()
 * @method  BoxEditor   getSingleObject()
 */
class BoxAction extends AbstractDatabaseObjectAction implements IToggleAction
{
    use TDatabaseObjectToggle;

    /**
     * @inheritDoc
     */
    protected $className = BoxEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.content.cms.canManageBox'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.content.cms.canManageBox'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.content.cms.canManageBox'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'getBoxConditionsTemplate', 'update'];

    /**
     * object type for which the conditions template is fetched
     * @var ObjectType
     */
    public $boxController;

    /**
     * @inheritDoc
     * @return  Box
     */
    public function create()
    {
        /** @var Box $box */
        $box = parent::create();

        // save box content
        if (!empty($this->parameters['content'])) {
            foreach ($this->parameters['content'] as $languageID => $content) {
                if (!empty($content['htmlInputProcessor'])) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $content['content'] = $content['htmlInputProcessor']->getHtml();
                }

                /** @var BoxContent $boxContent */
                $boxContent = BoxContentEditor::create([
                    'boxID' => $box->boxID,
                    'languageID' => $languageID ?: null,
                    'title' => $content['title'],
                    'content' => $content['content'] ?? '',
                    'imageID' => $content['imageID'] ?? null,
                ]);
                $boxContentEditor = new BoxContentEditor($boxContent);

                // save embedded objects
                if (!empty($content['htmlInputProcessor'])) {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $content['htmlInputProcessor']->setObjectID($boxContent->boxContentID);
                    if (MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
                        $boxContentEditor->update(['hasEmbeddedObjects' => 1]);
                    }
                } elseif ($box->boxType == 'html' || $box->boxType == 'tpl') {
                    if (
                        HtmlSimpleParser::getInstance()->parse(
                            'com.woltlab.wcf.box.content',
                            $boxContent->boxContentID,
                            $boxContent->content
                        )
                    ) {
                        $boxContentEditor->update(['hasEmbeddedObjects' => 1]);
                    }
                }
            }
        }

        // save box to page
        if (!empty($this->parameters['pageIDs'])) {
            $sql = "INSERT INTO wcf1_box_to_page
                                (boxID, pageID, visible)
                    VALUES      (?, ?, ?)";
            $statement = WCF::getDB()->prepare($sql);

            foreach ($this->parameters['pageIDs'] as $pageID) {
                $statement->execute([
                    $box->boxID,
                    $pageID,
                    $box->visibleEverywhere ? 0 : 1,
                ]);
            }
        }

        // save template
        if ($box->boxType == 'tpl') {
            if (!empty($this->parameters['content'])) {
                foreach ($this->parameters['content'] as $languageID => $content) {
                    \file_put_contents(
                        WCF_DIR . 'templates/' . $box->getTplName(($languageID ?: null)) . '.tpl',
                        $content['content']
                    );
                }
            }
        }

        return $box;
    }

    /**
     * @inheritDoc
     */
    public function update()
    {
        parent::update();

        $isRevert = (!empty($this->parameters['isRevert']));

        // update box content
        if (!empty($this->parameters['content'])) {
            foreach ($this->getObjects() as $box) {
                $versionData = [];
                $hasChanges = false;

                foreach ($this->parameters['content'] as $languageID => $content) {
                    if (!empty($content['htmlInputProcessor'])) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $content['content'] = $content['htmlInputProcessor']->getHtml();
                    }

                    $boxContent = BoxContent::getBoxContent($box->boxID, ($languageID ?: null));
                    $boxContentEditor = null;
                    if ($boxContent !== null) {
                        // update
                        $boxContentEditor = new BoxContentEditor($boxContent);
                        $boxContentEditor->update([
                            'title' => $content['title'],
                            'content' => $content['content'],
                            'imageID' => ($isRevert) ? $boxContent->imageID : $content['imageID'],
                        ]);

                        $versionData[] = $boxContent;
                        if ($boxContent->content != $content['content'] || $boxContent->title != $content['title']) {
                            $hasChanges = true;
                        }

                        $boxContent = BoxContent::getBoxContent($box->boxID, ($languageID ?: null));
                    } else {
                        /** @var BoxContent $boxContent */
                        $boxContent = BoxContentEditor::create([
                            'boxID' => $box->boxID,
                            'languageID' => $languageID ?: null,
                            'title' => $content['title'],
                            'content' => $content['content'],
                            'imageID' => ($isRevert) ? $content['imageID'] : null,
                        ]);
                        $boxContentEditor = new BoxContentEditor($boxContent);

                        $versionData[] = $boxContent;
                        $hasChanges = true;
                    }

                    // save embedded objects
                    if (!empty($content['htmlInputProcessor'])) {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $content['htmlInputProcessor']->setObjectID($boxContent->boxContentID);
                        if ($boxContent->hasEmbeddedObjects != MessageEmbeddedObjectManager::getInstance()->registerObjects($content['htmlInputProcessor'])) {
                            $boxContentEditor->update(['hasEmbeddedObjects' => $boxContent->hasEmbeddedObjects ? 0 : 1]);
                        }
                    } elseif ($box->boxType == 'html' || $box->boxType == 'tpl') {
                        if (
                            $boxContent->hasEmbeddedObjects != HtmlSimpleParser::getInstance()->parse(
                                'com.woltlab.wcf.box.content',
                                $boxContent->boxContentID,
                                $boxContent->content
                            )
                        ) {
                            $boxContentEditor->update(['hasEmbeddedObjects' => $boxContent->hasEmbeddedObjects ? 0 : 1]);
                        }
                    }
                }

                // save template
                if ($box->boxType == 'tpl') {
                    foreach ($this->parameters['content'] as $languageID => $content) {
                        $box->writeTemplate($languageID, $content['content']);
                    }
                }

                if ($hasChanges) {
                    $boxObj = new BoxVersionTracker($box->getDecoratedObject());
                    $boxObj->setContent($versionData);
                    VersionTracker::getInstance()->add('com.woltlab.wcf.box', $boxObj);
                }
            }
        }

        // save box to page
        if (isset($this->parameters['pageIDs'])) {
            $sql = "DELETE FROM wcf1_box_to_page
                    WHERE       boxID = ?";
            $deleteStatement = WCF::getDB()->prepare($sql);

            $sql = "INSERT INTO wcf1_box_to_page
                                (boxID, pageID, visible)
                    VALUES      (?, ?, ?)";
            $insertStatement = WCF::getDB()->prepare($sql);

            foreach ($this->getObjects() as $box) {
                $deleteStatement->execute([$box->boxID]);
                $visibleEverywhere = ($this->parameters['data']['visibleEverywhere'] ?? $box->visibleEverywhere);

                foreach ($this->parameters['pageIDs'] as $pageID) {
                    $insertStatement->execute([$box->boxID, $pageID, $visibleEverywhere ? 0 : 1]);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function validateDelete()
    {
        parent::validateDelete();

        foreach ($this->getObjects() as $object) {
            if (!$object->canDelete()) {
                throw new PermissionDeniedException();
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete()
    {
        $boxContentIDs = [];
        foreach ($this->getObjects() as $box) {
            foreach ($box->getBoxContents() as $languageID => $content) {
                if ($box->boxType == 'tpl') {
                    $file = WCF_DIR . 'templates/' . $box->getTplName(($languageID ?: null)) . '.tpl';
                    if (\file_exists($file)) {
                        @\unlink($file);
                    }
                }

                $boxContentIDs[] = $content->boxContentID;
            }
        }

        if (!empty($boxContentIDs)) {
            // update embedded objects
            MessageEmbeddedObjectManager::getInstance()->removeObjects('com.woltlab.wcf.box.content', $boxContentIDs);
        }

        parent::delete();
    }

    /**
     * Validates the 'getBoxConditionsTemplate' action.
     */
    public function validateGetBoxConditionsTemplate()
    {
        WCF::getSession()->checkPermissions(['admin.content.cms.canManageBox']);

        $this->readInteger('objectTypeID');
        $this->boxController = ObjectTypeCache::getInstance()->getObjectType($this->parameters['objectTypeID']);
        if ($this->boxController === null) {
            throw new UserInputException('objectTypeID');
        }
    }

    /**
     * Returns the template
     *
     * @return  mixed[]
     */
    public function getBoxConditionsTemplate()
    {
        return [
            'objectTypeID' => $this->boxController->objectTypeID,
            'template' => $this->boxController->getProcessor() instanceof IConditionBoxController ? $this->boxController->getProcessor()->getConditionsTemplate() : '',
        ];
    }
}
