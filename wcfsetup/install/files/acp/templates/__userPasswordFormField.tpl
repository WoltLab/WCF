<input type="password" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$field->getValue()}" {*
	*}class="long"{*
	*}{if $field->getAutoComplete() !== null} autocomplete="{$field->getAutoComplete()}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
	*}{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>
