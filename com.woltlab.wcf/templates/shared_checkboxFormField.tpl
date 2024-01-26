<dl id="{$field->getPrefixedId()}Container" {if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{if !$field->checkDependencies()} style="display: none;"{/if}>
	<dt></dt>
	<dd>
		<label>
			<input type="checkbox" {*
				*}id="{$field->getPrefixedId()}" {*
				*}name="{$field->getPrefixedId()}" {*
				*}value="1"{*
				*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
				*}{if $field->isRequired()} required{/if}{*
				*}{if $field->isImmutable()} disabled{/if}{*
				*}{if $field->getValue()} checked{/if}{*
				*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
			*}>
			{@$field->getLabel()}{if $field->isRequired()} <span class="formFieldRequired">*</span>{/if}
		</label>

		{include file='shared_formFieldDescription'}
		{include file='shared_formFieldErrors'}
		{include file='shared_formFieldDependencies'}
		{include file='shared_formFieldDataHandler'}
	</dd>
</dl>
