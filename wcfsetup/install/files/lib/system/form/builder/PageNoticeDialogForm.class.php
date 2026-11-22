<?php

namespace wcf\system\form\builder;

use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeBuilder;
use wcf\system\form\builder\field\BooleanFormField;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\wysiwyg\WysiwygFormField;
use wcf\system\WCF;

/**
 * Creates the dialog form for adding and editing page notices.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticeDialogForm
{
    private function __construct() {}

    public static function create(string $formId, ?PageNotice $pageNotice = null, bool $loadValues = true): Psr15BuilderDialogForm
    {
        $form = new Psr15BuilderDialogForm(
            $formId,
            WCF::getLanguage()->get($pageNotice === null ? 'wcf.pageNotice.add' : 'wcf.pageNotice.edit')
        );

        $typeChoices = [];
        foreach (PageNotice::TYPES as $type) {
            $typeChoices[$type] = 'wcf.pageNotice.type.' . $type;
        }

        $form->appendChildren([
            SelectFormField::create('type')
                ->label('wcf.pageNotice.type')
                ->required()
                ->phraseChoices($typeChoices)
                ->defaultValue('info')
                ->saveValueCallback(static function (PageNoticeBuilder $builder, SelectFormField $field): void {
                    $builder->setType($field->getSaveValue());
                })
                ->loadValueCallback(static function (PageNotice $object, IFormField $field): void {
                    $field->value($object->type);
                }),
            BooleanFormField::create('isInternal')
                ->label('wcf.pageNotice.isInternal')
                ->description('wcf.pageNotice.isInternal.description')
                ->saveValueCallback(static function (PageNoticeBuilder $builder, BooleanFormField $field): void {
                    $builder->setInternal($field->getSaveValue() === 1);
                })
                ->loadValueCallback(static function (PageNotice $object, IFormField $field): void {
                    $field->value($object->isInternal);
                }),
            WysiwygFormField::create('message')
                ->label('wcf.pageNotice.message')
                ->objectType(PageNotice::MESSAGE_OBJECT_TYPE)
                ->l10n()
                ->required()
                ->saveValueCallback(static function (PageNoticeBuilder $builder, WysiwygFormField $field): void {
                    $builder->setMessage($field->getL10nHtmlInputProcessors());
                })
                ->loadValueCallback(static function (PageNotice $object, IFormField $field): void {
                    $field->value($object->getL10nMessage());
                }),
        ]);

        $form->markRequiredFields(false);

        if ($pageNotice === null) {
            $form->formMode(IFormDocument::FORM_MODE_CREATE);
            $form->build();
        } else {
            $form->formMode(IFormDocument::FORM_MODE_UPDATE);
            $form->build();
            $form->updatedObject($pageNotice, $loadValues);
        }

        return $form;
    }
}
