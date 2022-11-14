{if $field->getSuffix() !== null}
	<div class="inputAddon">
{/if}

<input type="number" {*
	*}step="{@$field->getStep()}" {*
	*}id="{$field->getPrefixedId()}" {*
	*}name="{$field->getPrefixedId()}" {*
	*}value="{$field->getValue()}"{*
	*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
	*}{if $field->getAutoComplete() !== null} autocomplete="{$field->getAutoComplete()}"{/if}{*
	*}{if $field->isAutofocused()} autofocus{/if}{*
	*}{if $field->isRequired()} required{/if}{*
	*}{if $field->isImmutable()} disabled{/if}{*
	*}{if $field->getMinimum() !== null} min="{$field->getMinimum()}"{/if}{*
	*}{if $field->getMaximum() !== null} max="{$field->getMaximum()}"{/if}{*
	*}{if $field->getInputMode() !== null} inputmode="{$field->getInputMode()}"{/if}{*
	*}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}{*
	*}{if $field->getDocument()->isAjax()} data-dialog-submit-on-enter="true"{/if}{*
	*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
*}>

{if $field->getSuffix() !== null}
		<span class="inputSuffix">{@$field->getSuffix()}</span>
	</div>
{/if}
