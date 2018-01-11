<dl{if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class'}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	<dt><label for="{@$field->getPrefixedId()}">{@$field->getLabel()}</label></dt>
	<dd>
		<select id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}">
			{htmlOptions options=$field->getOptions() selected=$field->getValue()}
		</select>
		
		{include file='__formFieldDescription'}
		
		{include file='__formFieldErrors'}
	</dd>
</dl>
