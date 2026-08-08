<?php

namespace wcf\acp\form;

use wcf\data\bbcode\attribute\BBCodeAttribute;
use wcf\data\bbcode\attribute\BBCodeAttributeAction;
use wcf\data\bbcode\BBCode;
use wcf\data\bbcode\BBCodeAction;
use wcf\form\AbstractForm;
use wcf\http\Helper;
use wcf\system\language\I18nHandler;
use wcf\system\WCF;

/**
 * Shows the bbcode edit form.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class BBCodeEditForm extends BBCodeAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.bbcode.list';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.content.bbcode.canManageBBCode'];

    /**
     * bbcode object
     * @var ?BBCode
     */
    public $bbcode;

    /**
     * list of native bbcodes
     * @var string[]
     */
    public static $nativeBBCodes = [
        'b',
        'i',
        'u',
        's',
        'sub',
        'sup',
        'list',
        'align',
        'color',
        'size',
        'font',
        'url',
        'img',
        'email',
        'table',
    ];

    #[\Override]
    public function readParameters()
    {
        AbstractForm::readParameters();

        $this->bbcode = Helper::fetchObjectFromQueryParameter(BBCode::class);

        if (!\in_array($this->bbcode->bbcodeTag, self::$nativeBBCodes)) {
            I18nHandler::getInstance()->register('buttonLabel');
        }
    }

    #[\Override]
    protected function readButtonLabelFormParameter()
    {
        if (!\in_array($this->bbcode->bbcodeTag, self::$nativeBBCodes)) {
            parent::readButtonLabelFormParameter();
        }
    }

    #[\Override]
    protected function validateBBCodeTagUsage()
    {
        if ($this->bbcodeTag !== $this->bbcode->bbcodeTag) {
            parent::validateBBCodeTagUsage();
        }
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        if ($this->showButton) {
            $this->buttonLabel = 'wcf.editor.button.button' . $this->bbcode->bbcodeID;
            if (I18nHandler::getInstance()->isPlainValue('buttonLabel')) {
                I18nHandler::getInstance()->remove($this->buttonLabel);
                $this->buttonLabel = I18nHandler::getInstance()->getValue('buttonLabel');
            } else {
                I18nHandler::getInstance()->save('buttonLabel', $this->buttonLabel, 'wcf.editor', 1);
            }
        }

        // update bbcode
        $this->objectAction = new BBCodeAction([$this->bbcode], 'update', [
            'data' => \array_merge($this->additionalFields, [
                'bbcodeTag' => $this->bbcodeTag,
                'buttonLabel' => $this->buttonLabel,
                'className' => $this->className,
                'htmlClose' => $this->htmlClose,
                'htmlOpen' => $this->htmlOpen,
                'isBlockElement' => $this->isBlockElement ? 1 : 0,
                'isSourceCode' => $this->isSourceCode ? 1 : 0,
                'showButton' => $this->showButton ? 1 : 0,
                'wysiwygIcon' => $this->wysiwygIcon,
            ]),
        ]);
        $this->objectAction->executeAction();

        // clear existing attributes
        $sql = "DELETE FROM wcf1_bbcode_attribute
                WHERE       bbcodeID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->bbcode->bbcodeID]);

        foreach ($this->attributes as $attribute) {
            $attributeAction = new BBCodeAttributeAction([], 'create', [
                'data' => [
                    'bbcodeID' => $this->bbcode->bbcodeID,
                    'attributeNo' => $attribute->attributeNo,
                    'attributeHtml' => $attribute->attributeHtml,
                    'validationPattern' => $attribute->validationPattern,
                    'required' => $attribute->required,
                    'useText' => $attribute->useText,
                ],
            ]);
            $attributeAction->executeAction();
        }

        $this->saved();

        // show success message
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        if (empty($_POST)) {
            I18nHandler::getInstance()->setOptions(
                'buttonLabel',
                1,
                $this->bbcode->buttonLabel,
                'wcf.editor.button.button\d+'
            );
            $this->buttonLabel = $this->bbcode->buttonLabel;

            $this->attributes = BBCodeAttribute::getAttributesByBBCode($this->bbcode);
            $this->bbcodeTag = $this->bbcode->bbcodeTag;
            $this->htmlOpen = $this->bbcode->htmlOpen;
            $this->htmlClose = $this->bbcode->htmlClose;
            $this->isBlockElement = $this->bbcode->isBlockElement !== 0;
            $this->isSourceCode = $this->bbcode->isSourceCode !== 0;
            $this->className = $this->bbcode->className;
            $this->showButton = $this->bbcode->showButton !== 0;
            $this->wysiwygIcon = $this->bbcode->wysiwygIcon;
        }
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        I18nHandler::getInstance()->assignVariables(!empty($_POST));

        WCF::getTPL()->assign([
            'bbcode' => $this->bbcode,
            'action' => 'edit',
            'nativeBBCode' => \in_array($this->bbcode->bbcodeTag, self::$nativeBBCodes),
        ]);
    }
}
