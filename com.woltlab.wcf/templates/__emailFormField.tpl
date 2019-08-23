{include file='__formFieldHeader'}

<input type="email" {*
	*}id="{@$field->getPrefixedId()}" {*
	*}name="{@$field->getPrefixedId()}" {*
	*}value="{if !$field->isI18n() || !$field->hasI18nValues()}{$field->getValue()}{/if}" {*
	*}class="long"{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
	*}{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}{*
*}>

{if $field->isI18n()}
	{include file='multipleLanguageInputJavascript'}
{/if}

{include file='__formFieldFooter'}
