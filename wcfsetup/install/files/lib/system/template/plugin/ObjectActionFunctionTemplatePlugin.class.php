<?php

namespace wcf\system\template\plugin;

use wcf\system\language\LanguageFactory;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template function plugin which generate delete and toggle buttons for objects to be used in
 * combination with `WoltLabSuite/Core/Ui/Object/Action`.
 *
 * Required argument: `action` with value `delete` or `toggle`.
 * Optional arguments for all actions:
 *  - `objectId` (to override the objectId set via the `data-object-id` attribute by the closest
 *      `jsObjectActionObject` element)
 *  - `className` (to override the objectId set via the `data-object-action-class-name` attribute
 *      by the closest `jsObjectActionContainer` element)
 *  - attributes beginning with `parameter` are mapped to `data-object-action-parameter-*`
 *      attributes of the button
 *
 * One of the following aguments for `delete` action is required:
 *  - `objectTitle`: name of the object used in the `wcf.button.delete.confirmMessage`
 *      confirmation language item
 *  - `confirmMessage`: confirmation message or confirmation language item
 *
 * Arguments for `toggle` action:
 *  - `isDisabled` (required): indicates the current toggle state of the relevant object
 *  - `disableTitle`: title or language item with the title of the button for the disable action
 *  - `enableTitle`: title or language item with the title of the button for the enable action
 *
 * Examples:
 *
 *  {objectAction action="delete" objectTitle=$object->getTitle()}
 *  {objectAction action="delete" confirmMessage='wcf.foo.delete.confirmMessage' parameterFoo='bar'}
 *  {objectAction action="toggle" isDisabled=$object->isDisabled}
 *  {objectAction action="toggle" isDisabled=$object->isDisabled disableTitle='wcf.foo.button.disable' enableTitle='wcf.foo.button.enable'}
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Template\Plugin
 * @since   5.4
 */
class ObjectActionFunctionTemplatePlugin implements IFunctionTemplatePlugin
{
    private const SUPPORTED_ACTIONS = [
        'delete',
        'toggle',
    ];

    /**
     * @inheritDoc
     */
    public function execute($tagArgs, TemplateEngine $tplObj)
    {
        if (!isset($tagArgs['action'])) {
            throw new \InvalidArgumentException("Missing 'action' argument.");
        }
        $action = $tagArgs['action'];
        if (!\in_array($action, static::SUPPORTED_ACTIONS)) {
            throw new \InvalidArgumentException("Unsupported action '{$action}'.");
        }

        $additionalAttributes = '';
        if (isset($tagArgs['objectId'])) {
            $additionalAttributes .= " data-object-id=\"{$tagArgs['objectId']}\"";
        }
        $className = null;
        if (isset($tagArgs['className'])) {
            $additionalAttributes .= " data-object-action-class-name=\"{$tagArgs['className']}\"";
        }
        foreach ($tagArgs as $key => $value) {
            if (\preg_match('~^parameter.+$~', $key)) {
                $additionalAttributes .= \sprintf(
                    ' data-object-action-%s="%s"',
                    \strtolower(\preg_replace(
                        '~([A-Z])~',
                        '-$1',
                        $key
                    )),
                    StringUtil::encodeHTML($value)
                );
            }
        }

        $language = LanguageFactory::getInstance()->getLanguage($tplObj->languageID);

        switch ($action) {
            case 'delete':
                if (isset($tagArgs['objectTitle'])) {
                    $confirmMessage = StringUtil::encodeHTML(
                        $language->getDynamicVariable(
                            'wcf.button.delete.confirmMessage',
                            [
                                'objectTitle' => $tagArgs['objectTitle'],
                            ]
                        )
                    );
                } elseif (isset($tagArgs['confirmMessage'])) {
                    $confirmMessage = StringUtil::encodeHTML(
                        $language->getDynamicVariable($tagArgs['confirmMessage'])
                    );
                } else {
                    throw new \InvalidArgumentException("Missing 'objectTitle' or 'confirmMessage' argument for 'delete' action.");
                }

                $title = $language->getDynamicVariable('wcf.global.button.delete');

                return <<<HTML
<span class="icon icon16 fa-times jsObjectAction jsTooltip pointer" title="{$title}" data-object-action="delete" data-confirm-message="{$confirmMessage}"{$additionalAttributes}></span>
HTML;

                break;

            case 'toggle':
                if (!isset($tagArgs['isDisabled'])) {
                    throw new \InvalidArgumentException("Missing 'isDisabled' argument for 'toggle' action.");
                }

                $icon = 'fa-check-square-o';
                $title = $language->getDynamicVariable('wcf.global.button.disable');
                if ($tagArgs['isDisabled']) {
                    $icon = 'fa-square-o';
                    $title = $language->getDynamicVariable('wcf.global.button.enable');
                }
                $title = StringUtil::encodeHTML($title);

                if (isset($tagArgs['disableTitle'])) {
                    $disableTitle = StringUtil::encodeHTML($language->getDynamicVariable($tagArgs['disableTitle']));
                    $additionalAttributes .= 'data-disable-title="' . $disableTitle . '"';
                    if (!$tagArgs['isDisabled']) {
                        $title = $disableTitle;
                    }
                }
                if (isset($tagArgs['enableTitle'])) {
                    $enableTitle = StringUtil::encodeHTML($language->getDynamicVariable($tagArgs['enableTitle']));
                    $additionalAttributes .= 'data-enable-title="' . $enableTitle . '"';
                    if ($tagArgs['isDisabled']) {
                        $title = $enableTitle;
                    }
                }

                return <<<HTML
<span class="icon icon16 {$icon} jsObjectAction jsTooltip pointer" title="{$title}" data-object-action="toggle"{$additionalAttributes}></span>
HTML;

            default:
                throw new \LogicException("Unreachable.");
        }
    }
}
