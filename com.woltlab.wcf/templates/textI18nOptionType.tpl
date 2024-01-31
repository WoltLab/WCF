<input type="{@$inputType}" id="{$option->optionName}" name="{$option->optionName}" value="{$i18nPlainValues[$option->optionName]}"{if $option->required} required{/if} class="long">
{include file='shared_multipleLanguageInputJavascript' elementIdentifier=$option->optionName forceSelection=false}
