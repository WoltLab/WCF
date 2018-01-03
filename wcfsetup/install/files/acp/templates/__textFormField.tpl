<dl{if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class'}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	<dt><label for="{@$field->getPrefixedId()}">{@$field->getLabel()}</label></dt>
	<dd>
		<input type="text" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="{$field->getValue()}" class="long"{if $field->isAutofocused()} autofocus{/if}{if $field->isRequired()} required{/if}{if $field->isImmutable()} disabled{/if}{if $field->getMinimumLength() !== null} minlength="{$field->getMinimumLength()}"{/if}{if $field->getMaximumLength() !== null} maxlength="{$field->getMaximumLength()}"{/if}{if $field->getPlaceholder() !== null} placeholder="{$field->getPlaceholder()}"{/if}">
		
		{include file='__formFieldDescription'}
		
		{include file='__formFieldErrors'}
		
		{if $field->isI18n()}
			{include file='multipleLanguageInputJavascript'}
		{/if}
	</dd>
</dl>
