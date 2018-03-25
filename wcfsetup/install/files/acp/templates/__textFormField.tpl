{include file='__formFieldHeader'}

<input type="text" {*
	*}id="{@$field->getPrefixedId()}" {*
	*}name="{@$field->getPrefixedId()}" {*
	*}value="{if !$field->isI18n() || !$field->hasI18nValues()}{$field->getValue()}{/if}" {*
	*}class="long"{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getMinimumLength() !== null} minlength="{$field->getMinimumLength()}"{/if}{*
	*}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
*}>

{if $field->isI18n()}
	{include file='multipleLanguageInputJavascript'}
{/if}

{include file='__formFieldFooter'}
