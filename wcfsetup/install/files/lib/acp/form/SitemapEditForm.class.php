<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\data\IStorableObject;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IntegerFormField;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\system\worker\SitemapRebuildWorker;

/**
 * Shows the sitemap edit form.
 *
 * @author      Olaf Braun, Joshua Ruesweg
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<ObjectType>
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
     * @inheritDoc
     */
    public $formAction = 'edit';

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

        $this->formObject = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.sitemap.object',
            $queryParameters['objectType']
        );

        if ($this->formObject === null) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

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
                        ->value('monthly')
                        ->required(),
                    IntegerFormField::create('rebuildTime')
                        ->label('wcf.acp.sitemap.rebuildTime')
                        ->description('wcf.acp.sitemap.rebuildTime.description')
                        ->suffix('wcf.acp.option.suffix.seconds')
                        ->minimum(0)
                        ->value(172800)
                        ->addFieldClass('short'),
                    BooleanFormField::create('isDisabled')
                        ->label('wcf.acp.sitemap.isDisabled')
                ])
        ]);
    }

    #[\Override]
    protected function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'registryDataProcessor',
                    null,
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof ObjectType);
                        $sitemapData = RegistryHandler::getInstance()->get(
                            'com.woltlab.wcf',
                            SitemapRebuildWorker::REGISTRY_PREFIX . $object->objectType
                        );
                        $sitemapData = @\unserialize($sitemapData);

                        if (\is_array($sitemapData)) {
                            $data["changeFreq"] = $sitemapData['changeFreq'];
                            $data["rebuildTime"] = $sitemapData['rebuildTime'];
                            $data["isDisabled"] = $sitemapData['isDisabled'];
                        } else {
                            if ($object->changeFreq !== null) {
                                $data["changeFreq"] = $object->changeFreq;
                            }
                            if ($object->rebuildTime !== null) {
                                $data["rebuildTime"] = $object->rebuildTime;
                            }
                            if ($object->isDisabled !== null) {
                                $data["isDisabled"] = $object->isDisabled;
                            }
                        }

                        return $data;
                    }
                )
            );
    }

    #[\Override]
    public function save()
    {
        AbstractForm::save();

        $formData = $this->form->getData();
        if (!isset($formData['data'])) {
            $formData['data'] = [];
        }
        $formData['data'] = \array_merge($this->additionalFields, $formData['data']);

        RegistryHandler::getInstance()->set(
            'com.woltlab.wcf',
            SitemapRebuildWorker::REGISTRY_PREFIX . $this->formObject->objectType,
            \serialize($formData['data'])
        );

        $this->saved();
        WCF::getTPL()->assign('success', true);
    }

    #[\Override]
    protected function setFormAction()
    {
        $this->form->action(LinkHandler::getInstance()->getControllerLink(static::class, [
            'objectType' => $this->formObject->objectType
        ]));
    }
}
