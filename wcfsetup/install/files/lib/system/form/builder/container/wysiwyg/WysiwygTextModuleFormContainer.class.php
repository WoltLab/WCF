<?php

namespace wcf\system\form\builder\container\wysiwyg;

use wcf\action\TextModuleEditAction;
use wcf\data\text\module\TextModule;
use wcf\system\request\LinkHandler;
use wcf\system\style\FontAwesomeIcon;

/**
 * Represents the tab below a WYSIWYG editor that lists the text modules and
 * allows them to be inserted into the editor.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class WysiwygTextModuleFormContainer extends WysiwygTabFormContainer
{
    protected $templateName = 'shared_wysiwygTextModuleFormContainer';

    // @phpstan-ignore constructor.missingParentCall
    public function __construct()
    {
        $this->icon(FontAwesomeIcon::fromValues('file-lines'))
            ->name('textModules')
            ->label('wcf.textModule.messageTab');
    }

    #[\Override]
    public function isAvailable()
    {
        return $this->available && TextModule::canUse();
    }

    /**
     * The tab has no form children because its content is loaded on demand,
     * the default implementation would therefore always hide the tab.
     */
    #[\Override]
    public function checkDependencies(): bool
    {
        foreach ($this->dependencies as $dependency) {
            if (!$dependency->checkDependency() || !$dependency->getField()->checkDependencies()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the endpoint of the dialog to create a new text module.
     */
    public function getAddEndpoint(): string
    {
        return LinkHandler::getInstance()->getControllerLink(TextModuleEditAction::class);
    }
}
