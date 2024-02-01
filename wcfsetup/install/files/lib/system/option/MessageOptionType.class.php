<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\data\smiley\SmileyCache;
use wcf\system\bbcode\BBCodeHandler;
use wcf\system\exception\UserInputException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\html\upcast\HtmlUpcastProcessor;
use wcf\system\WCF;
use wcf\util\ArrayUtil;

/**
 * Option type implementation for message.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MessageOptionType extends TextareaOptionType
{
    /**
     * @var HtmlInputProcessor
     */
    protected $htmlInputProcessor;

    /**
     * object type for definition 'com.woltlab.wcf.message'
     * @var string
     */
    protected $messageObjectType = '';

    /**
     * @inheritDoc
     */
    public function getData(Option $option, $newValue)
    {
        if (!$this->messageObjectType) {
            $this->messageObjectType = $option->messageObjectType;
        }
        if (empty($this->messageObjectType)) {
            throw new \RuntimeException("Message object type '" . $option->optionName . "' requires an object type for definition 'com.woltlab.wcf.message'.");
        }

        $permission = $option->disallowedbbcodepermission ?: 'user.message.disallowedBBCodes';
        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            ArrayUtil::trim(WCF::getSession()->getPermission($permission))
        ));

        $this->htmlInputProcessor = new HtmlInputProcessor();

        // the object id is fixed because options are not tied to a specific object
        // especially when used in the user option context, such as "about me"
        $this->htmlInputProcessor->process($newValue, $this->messageObjectType, 0);

        return parent::getData($option, $this->htmlInputProcessor->getHtml());
    }

    /**
     * @inheritDoc
     */
    public function getFormElement(Option $option, $value)
    {
        $permission = $option->disallowedbbcodepermission ?: 'user.message.disallowedBBCodes';
        BBCodeHandler::getInstance()->setDisallowedBBCodes(\explode(
            ',',
            ArrayUtil::trim(WCF::getSession()->getPermission($permission))
        ));

        $upcastProcessor = new HtmlUpcastProcessor();
        $upcastProcessor->process($value ?? '', $this->messageObjectType);

        WCF::getTPL()->assign([
            'defaultSmilies' => SmileyCache::getInstance()->getCategorySmilies(),
            'option' => $option,
            'value' => $upcastProcessor->getHtml(),
        ]);

        return WCF::getTPL()->fetch('messageOptionType');
    }

    /**
     * @inheritDoc
     */
    public function validate(Option $option, $newValue)
    {
        parent::validate($option, $newValue);

        $disallowedBBCodes = $this->htmlInputProcessor->validate();
        if (!empty($disallowedBBCodes)) {
            WCF::getTPL()->assign('disallowedBBCodes', $disallowedBBCodes);
            throw new UserInputException($option->optionName, 'disallowedBBCodes');
        }
    }
}
