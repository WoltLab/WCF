<dl{if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class'}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	<dt><label for="{@$field->getPrefixedId()}">{@$field->getLabel()}</label></dt>
	<dd>
		{if $field->getSuffix() !== null}
			<div class="inputAddon">
		{/if}
		<input type="number" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="{$field->getValue()}" class="short"{if $field->isAutofocused()} autofocus{/if}{if $field->isRequired()} required{/if}{if $field->isImmutable()} disabled{/if}{if $field->getMinimum() !== null} min="{$field->getMinimum()}"{/if}{if $field->getMaximum() !== null} max="{$field->getMaximum()}"{/if}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}">
		{if $field->getSuffix() !== null}
				<span class="inputSuffix">{@$field->getSuffix()}</span>
			</div>
		{/if}
		
		{include file='__formFieldDescription'}
		
		{include file='__formFieldErrors'}
	</dd>
</dl>
