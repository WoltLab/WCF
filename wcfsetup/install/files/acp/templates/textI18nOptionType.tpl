<input type="{@$inputType}" id="{$option->optionName}" name="{$option->optionName}" value="{$i18nPlainValues[$option->optionName]}" class="long">
{include file='shared_multipleLanguageInputJavascript' elementIdentifier=$option->optionName forceSelection=$option->requireI18n}
