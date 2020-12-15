<dl id="{@$field->getPrefixedId()}Container" {if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class' glue=' '}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{if !$field->checkDependencies()} style="display: none;"{/if}>
    <dt></dt>
    <dd>
        <label>
            <input type="checkbox" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="1"{if $field->isRequired()} required{/if}{if $field->isImmutable()} disabled{/if}{if $field->getValue()} checked{/if}>
            {@$field->getLabel()}{if $field->isRequired()} <span class="formFieldRequired">*</span>{/if}
        </label>
        
        {include file='__formFieldDescription'}
		{include file='__formFieldErrors'}
		{include file='__formFieldDependencies'}
		{include file='__formFieldDataHandler'}
    </dd>
</dl>
