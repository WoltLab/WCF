<?php

namespace wcf\data\template;

use wcf\data\AbstractDatabaseObjectAction;
use wcf\system\language\LanguageFactory;

/**
 * Executes template-related actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractDatabaseObjectAction<Template, TemplateEditor>
 */
class TemplateAction extends AbstractDatabaseObjectAction
{
    /**
     * @inheritDoc
     */
    protected $className = TemplateEditor::class;

    /**
     * @inheritDoc
     */
    protected $permissionsCreate = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    protected $permissionsDelete = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    protected $permissionsUpdate = ['admin.template.canManageTemplate'];

    /**
     * @inheritDoc
     */
    protected $requireACP = ['create', 'delete', 'update'];

    #[\Override]
    public function create()
    {
        /** @var Template $template */
        $template = parent::create();

        if (isset($this->parameters['source'])) {
            $editor = new TemplateEditor($template);
            $editor->setSource($this->parameters['source']);
        }

        return $template;
    }

    #[\Override]
    public function delete()
    {
        $count = parent::delete();

        LanguageFactory::getInstance()->deleteLanguageCache();

        return $count;
    }

    #[\Override]
    public function update()
    {
        parent::update();

        foreach ($this->getObjects() as $template) {
            // rename file
            $templateName = ($this->parameters['data']['templateName'] ?? $template->templateName);
            $templateGroupID = ($this->parameters['data']['templateGroupID'] ?? $template->templateGroupID);
            if ($templateName !== $template->templateName || $templateGroupID !== $template->templateGroupID) {
                $template->rename($templateName, $templateGroupID);
            }

            // update source
            if (isset($this->parameters['source'])) {
                $template->setSource($this->parameters['source']);
            }
        }
    }
}
