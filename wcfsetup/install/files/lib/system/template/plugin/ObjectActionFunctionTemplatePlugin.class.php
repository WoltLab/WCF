<?php

namespace wcf\system\template\plugin;

use wcf\system\language\LanguageFactory;
use wcf\system\template\TemplateEngine;
use wcf\util\StringUtil;

/**
 * Template function plugin which generate delete and toggle buttons for objects to be used in
 * combination with `WoltLabSuite/Core/Ui/Object/Action`.
 * 
 * TODO: More information and examples
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
        if (!in_array($action, static::SUPPORTED_ACTIONS)) {
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
                $additionalAttributes .= sprintf(
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
                    $confirmMessage = StringUtil::encodeHTML($tplObj->fetchString(
                        $tplObj->getCompiler()->compileString(
                            'wcf.global.button.delete.confirmMessage',
                            $language->get('wcf.global.button.delete.confirmMessage')
                        )['template'],
                        [
                            'objectTitle' => $tagArgs['objectTitle'],
                        ]
                    ));
                }
                else if (isset($tagArgs['confirmMessage'])) {
                    $confirmMessage = StringUtil::encodeHTML($tplObj->fetchString(
                        $tplObj->getCompiler()->compileString(
                            $tagArgs['confirmMessage'],
                            $language->get($tagArgs['confirmMessage'])
                        )['template']));
                }
                else {
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
                
                return <<<HTML
<span class="icon icon16 {$icon} jsObjectAction jsTooltip pointer" title="{$title}" data-object-action="toggle"{$additionalAttributes}></span>
HTML;
                
            default:
                throw new \LogicException("Unreachable.");
        }
    }
}
