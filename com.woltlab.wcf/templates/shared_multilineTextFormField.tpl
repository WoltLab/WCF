<textarea id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}rows="{$field->getRows()}"{*
	*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{if $field->getAutoComplete() !== null} autocomplete="{$field->getAutoComplete()}"{/if}{*
	*}{if $field->getPattern() !== null} pattern="{$field->getPattern()}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getMinimumLength() !== null} minlength="{$field->getMinimumLength()}"{/if}{*
	*}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{*
	*}{if $field->getInputMode() !== null} inputmode="{$field->getInputMode()}"{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>{$field->getValue()}</textarea>

{if $field->isI18n()}
	{include file='multipleLanguageInputJavascript'}
{/if}
