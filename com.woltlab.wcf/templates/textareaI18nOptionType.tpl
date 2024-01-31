<textarea id="{$option->optionName}" name="{$option->optionName}" cols="40" rows="10"{if $option->required} required{/if}>{$i18nPlainValues[$option->optionName]}</textarea>
{include file='shared_multipleLanguageInputJavascript' elementIdentifier=$option->optionName forceSelection=false}
