#!/usr/bin/env php
<?php
// @codingStandardsIgnoreFile

/**
 * Helper script to migrate include templates to new shared templates in WoltLab Suite before 6.1.
 * Replace in all `.tpl` files the `{include file='__oldTemplateName'}` with the new template name.
 *
 * @author Olaf Braun
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

if (\PHP_SAPI !== 'cli') {
    exit;
}

// old-template => new-template
$templates = [
    '__wysiwygPreviewFormButton' => 'shared_wysiwygPreviewFormButton',
    '__formButton' => 'shared_formButton',
    '__wysiwygSmileyFormContainer' => 'shared_wysiwygSmileyFormContainer',
    '__wysiwygTabMenuFormContainer' => 'shared_wysiwygTabMenuFormContainer',
    '__formContainer' => 'shared_formContainer',
    '__rowFormContainer' => 'shared_rowFormContainer',
    '__rowFormFieldContainer' => 'shared_rowFormFieldContainer',
    '__suffixFormFieldContainer' => 'shared_suffixFormFieldContainer',
    '__tabFormContainer' => 'shared_tabFormContainer',
    '__tabMenuFormContainer' => 'shared_tabMenuFormContainer',
    '__tabTabMenuFormContainer' => 'shared_tabTabMenuFormContainer',
    '__simpleAclFormField' => 'shared_simpleAclFormField',
    '__aclFormField' => 'shared_aclFormField',
    '__bbcodeAttributesFormField' => 'shared_bbcodeAttributesFormField',
    '__emptyFormFieldDependency' => 'shared_emptyFormFieldDependency',
    '__isNotClickedFormFieldDependency' => 'shared_isNotClickedFormFieldDependency',
    '__nonEmptyFormFieldDependency' => 'shared_nonEmptyFormFieldDependency',
    '__valueFormFieldDependency' => 'shared_valueFormFieldDependency',
    '__valueIntervalFormFieldDependency' => 'shared_valueIntervalFormFieldDependency',
    '__labelFormField' => 'shared_labelFormField',
    '__contentLanguageFormField' => 'shared_contentLanguageFormField',
    '__singleMediaSelectionFormField' => 'shared_singleMediaSelectionFormField',
    '__pollOptionsFormField' => 'shared_pollOptionsFormField',
    '__tagFormField' => 'shared_tagFormField',
    '__userFormField' => 'shared_userFormField',
    '__usernameFormField' => 'shared_usernameFormField',
    '__userPasswordFormField' => 'shared_userPasswordFormField',
    '__formFieldError' => 'shared_formFieldError',
    '__wysiwygAttachmentFormField' => 'shared_wysiwygAttachmentFormField',
    '__wysiwygFormField' => 'shared_wysiwygFormField',
    '__numericFormField' => 'shared_numericFormField',
    '__booleanFormField' => 'shared_booleanFormField',
    '__buttonFormField' => 'shared_buttonFormField',
    '__captchaFormField' => 'shared_captchaFormField',
    '__checkboxFormField' => 'shared_checkboxFormField',
    '__colorFormField' => 'shared_colorFormField',
    '__dateFormField' => 'shared_dateFormField',
    '__emailFormField' => 'shared_emailFormField',
    '__hiddenFormField' => 'shared_hiddenFormField',
    '__iconFormField' => 'shared_iconFormField',
    '__itemListFormField' => 'shared_itemListFormField',
    '__multilineTextFormField' => 'shared_multilineTextFormField',
    '__multipleSelectionFormField' => 'shared_multipleSelectionFormField',
    '__passwordFormField' => 'shared_passwordFormField',
    '__radioButtonFormField' => 'shared_radioButtonFormField',
    '__ratingFormField' => 'shared_ratingFormField',
    '__selectFormField' => 'shared_selectFormField',
    '__sourceCodeFormField' => 'shared_sourceCodeFormField',
    '__uploadFormField' => 'shared_uploadFormField',
    '__wysiwygSmileyFormNode' => 'shared_wysiwygSmileyFormNode',
    '__form' => 'shared_form',
    '__formContainerChildren' => 'shared_formContainerChildren',
    '__formContainerDependencies' => 'shared_formContainerDependencies',
    '__formField' => 'shared_formField',
    '__formFieldDependencies' => 'shared_formFieldDependencies',
    '__formFieldDescription' => 'shared_formFieldDescription',
    '__formFieldErrors' => 'shared_formFieldErrors',
    '__formFieldDataHandler' => 'shared_formFieldDataHandler',
    '__singleSelectionFormField' => 'shared_singleSelectionFormField',
    '__mediaSetCategoryDialog' => 'shared_mediaSetCategoryDialog',
    '__messageQuoteManager' => 'shared_messageQuoteManager',
    '__topReaction' => 'shared_topReaction',
    '__wysiwygCmsToolbar' => 'shared_wysiwygCmsToolbar',
    'aclPermissionJavaScript' => 'shared_aclPermissionJavaScript',
    'aclSimple' => 'shared_aclSimple',
    'articleAddDialog' => 'shared_articleAddDialog',
    'benchmark' => 'shared_benchmark',
    'booleanOptionType' => 'shared_booleanOptionType',
    'booleanSearchableOptionType' => 'shared_booleanSearchableOptionType',
    'captcha' => 'shared_captcha',
    'captchaQuestion' => 'shared_captchaQuestion',
    'categoryOptionList' => 'shared_categoryOptionList',
    'checkboxesOptionType' => 'shared_checkboxesOptionType',
    'checkboxesSearchableOptionType' => 'shared_checkboxesSearchableOptionType',
    'codeMetaCode' => 'shared_codeMetaCode',
    'codemirror' => 'shared_codemirror',
    'colorPickerJavaScript' => 'shared_colorPickerJavaScript',
    'fontAwesomeJavaScript' => 'shared_fontAwesomeJavaScript',
    'formError' => 'shared_formError',
    'formNotice' => 'shared_formNotice',
    'formSuccess' => 'shared_formSuccess',
    'languageChooser' => 'shared_languageChooser',
    'lineBreakSeparatedTextOptionType' => 'shared_lineBreakSeparatedTextOptionType',
    'mediaManager' => 'shared_mediaManager',
    'messageFormAttachments' => 'shared_messageFormAttachments',
    'messageTableOfContents' => 'shared_messageTableOfContents',
    'multipleLanguageInputJavascript' => 'shared_multipleLanguageInputJavascript',
    'passwordStrengthLanguage' => 'shared_passwordStrengthLanguage',
    'quoteMetaCode' => 'shared_quoteMetaCode',
    'radioButtonSearchableOptionType' => 'shared_radioButtonSearchableOptionType',
    'recaptcha' => 'shared_recaptcha',
    'scrollablePageCheckboxList' => 'shared_scrollablePageCheckboxList',
    'sitemapEnd' => 'shared_sitemapEnd',
    'sitemapEntry' => 'shared_sitemapEntry',
    'sitemapIndex' => 'shared_sitemapIndex',
    'sitemapStart' => 'shared_sitemapStart',
    'trophyImage' => 'shared_trophyImage',
    'unfurlUrl' => 'shared_unfurlUrl',
    'uploadFieldComponent' => 'shared_uploadFieldComponent',
    'userBBCodeTag' => 'shared_bbcode_user',
    'userConditions' => 'shared_userConditions',
    'userOptionsCondition' => 'shared_userOptionsCondition',
    'worker' => 'shared_worker',
    'wysiwyg' => 'shared_wysiwyg',
    'groupBBCodeTag' => 'shared_bbcode_group',
    '__videoAttachmentBBCode' => 'shared_bbcode_attach_video',
    '__audioAttachmentBBCode' => 'shared_bbcode_attach_audio',
    'mediaBBCodeTag' => 'shared_bbcode_wsm',
    'articleBBCodeTag' => 'shared_bbcode_wsa',
    '__multiPageCondition' => 'shared_multiPageCondition',
    '__multilineItemListFormField' => 'shared_multilineItemListFormField',
    'imageViewer' => 'shared_imageViewer',
];
if ($argc !== 2) {
    echo "ERROR: Expected a single argument to the directory that should be used to recursively replace template includes.\n";
    exit(1);
}
$directory = realpath($argv[1]);
if (!is_dir($directory)) {
    echo "ERROR: The provided directory does not exist or is not accessible.\n";
    exit(1);
}

function replaceInFiles(string $path): int
{
    $directory = new RecursiveDirectoryIterator($path);
    $filter = new RecursiveCallbackFilterIterator(
        $directory,
        function (SplFileInfo $current) {
            $filename = $current->getFilename();
            if ($filename === '.' || $filename === '..') {
                return false;
            }

            if ($current->isDir()) {
                return true;
            }

            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            return $fileExtension === 'tpl';
        }
    );

    $updatedFiles = 0;

    $iterator = new RecursiveIteratorIterator($filter);
    foreach ($iterator as $fileInfo) {
        \assert($fileInfo instanceof SplFileInfo);
        if (replaceTemplateInclude($fileInfo->getPathname())) {
            $updatedFiles++;
        }
    }

    return $updatedFiles;
}

function replaceTemplateInclude(string $filename): bool
{
    global $templates;
    $content = file_get_contents($filename);
    $content = preg_replace_callback(
        '~\{include.*(?<fileInclude>file=[\'\"](?<templateName>' . \implode('|', \array_keys($templates)) . ')[\'\"]).*}~',
        function (array $matches) use ($templates): string {
            [
                'fileInclude' => $fileInclude,
                'templateName' => $templateName
            ] = $matches;
            if (!isset($templates[$templateName])) {
                return $matches[0];
            }
            // replace the old template name with the new one
            return str_replace(
                $fileInclude,
                str_replace($templateName, $templates[$templateName], $fileInclude),
                $matches[0]
            );
        },
        $content,
        -1,
        $count
    );

    if ($count > 0) {
        file_put_contents($filename, $content);
        return true;
    }

    return false;
}

$replacedFiles = replaceInFiles($directory);
echo "Replaced {$replacedFiles} files.\n";
