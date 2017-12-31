<dl{if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class'}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	<dt><label for="{@$field->getPrefixedId()}">{@$field->getLabel()}</label></dt>
	<dd>
		<ol class="flexibleButtonGroup">
			<li>
				<input type="radio" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="1"{if $field->isAutofocused()} autofocus{/if}{if $field->isRequired()} required{/if}{if $field->isImmutable()} disabled{/if}{if $field->getValue()} checked{/if}>
				<label for="{@$field->getPrefixedId()}" class="green"><span class="icon icon16 fa-check"></span> {lang}wcf.global.form.boolean.yes{/lang}</label>
			</li>
			<li>
				<input type="radio" id="{@$field->getPrefixedId()}_no" name="{@$field->getPrefixedId()}" value="0" name="{@$field->getPrefixedId()}"{if $field->isImmutable()} disabled{/if}{if !$field->getValue()} checked{/if}>
				<label for="{@$field->getPrefixedId()}_no" class="red"><span class="icon icon16 fa-times"></span> {lang}wcf.global.form.boolean.no{/lang}</label>
			</li>
		</ol>
		
		{include file='__formFieldDescription'}
		
		{include file='__formFieldErrors'}
	</dd>
</dl>
