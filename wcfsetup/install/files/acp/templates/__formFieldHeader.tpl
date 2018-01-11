<dl id="{@$field->getPrefixedId()}Container" {if !$field->getClasses()|empty} class="{implode from=$field->getClasses() item='class'}{$class}{/implode}"{/if}{foreach from=$field->getAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}>
	<dt><label for="{@$field->getPrefixedId()}">{@$field->getLabel()}</label></dt>
	<dd>
