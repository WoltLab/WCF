<textarea id="{$option->optionName}" name="{$option->optionName}" {if $option->required}required="required"{/if} cols="40" rows="10">{$i18nPlainValues[$option->optionName]}</textarea>
{include file='multipleLanguageInputJavascript' elementIdentifier=$option->optionName forceSelection=false}
