<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\request\LinkHandler;
use wcf\system\sitemap\object\RegisteredSitemapObject;
use wcf\system\sitemap\SitemapHandler;
use wcf\system\WCF;

/**
 * Shows the sitemap edit form.
 *
 * @author      Olaf Braun, Joshua Ruesweg
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<null>
 */
class SitemapEditForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $templateName = 'sitemapEdit';

    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.maintenance.sitemap';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.management.canRebuildData'];

    /**
     * @since 6.3
     */
    public RegisteredSitemapObject $sitemapObject;

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        objectType: non-empty-string
                    }
                    EOT
            );
        } catch (MappingError) {
            throw new IllegalLinkException();
        }

        $sitemapObject = SitemapHandler::getInstance()->getObject($queryParameters['objectType']);
        if ($sitemapObject === null) {
            throw new IllegalLinkException();
        }

        $this->sitemapObject = $sitemapObject;
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->formMode(IFormDocument::FORM_MODE_UPDATE);

        $this->form->appendChildren([
            FormContainer::create('section')
                ->appendChildren([
                    SingleSelectionFormField::create('changeFreq')
                        ->label('wcf.acp.sitemap.changeFreq')
                        ->options([
                            'always' => 'wcf.acp.sitemap.changeFreq.always',
                            'hourly' => 'wcf.acp.sitemap.changeFreq.hourly',
                            'daily' => 'wcf.acp.sitemap.changeFreq.daily',
                            'weekly' => 'wcf.acp.sitemap.changeFreq.weekly',
                            'monthly' => 'wcf.acp.sitemap.changeFreq.monthly',
                            'yearly' => 'wcf.acp.sitemap.changeFreq.yearly',
                            'never' => 'wcf.acp.sitemap.changeFreq.never',
                        ])
                        ->value(SitemapHandler::getInstance()->getChangeFreq($this->sitemapObject))
                        ->required(),
                    IntegerFormField::create('rebuildTime')
                        ->label('wcf.acp.sitemap.rebuildTime')
                        ->description('wcf.acp.sitemap.rebuildTime.description')
                        ->suffix('wcf.acp.option.suffix.seconds')
                        ->minimum(0)
                        ->value(SitemapHandler::getInstance()->getRebuildTime($this->sitemapObject))
                        ->addFieldClass('short'),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.sitemap.isDisabled')
                        ->value(SitemapHandler::getInstance()->isDisabled($this->sitemapObject))
                ])
        ]);
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $formData = $this->form->getData()['data'] ?? [];

        SitemapHandler::getInstance()->setConfiguration(
            $this->sitemapObject,
            $formData['changeFreq'],
            (int)$formData['rebuildTime'],
            (bool)$formData['isDisabled']
        );

        $this->saved();
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'sitemapObject' => $this->sitemapObject,
        ]);
    }

    #[\Override]
    protected function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
            'objectType' => $this->sitemapObject->getObjectName()
        ]));
    }
}
