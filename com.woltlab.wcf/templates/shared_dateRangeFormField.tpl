<input
	type="{if $field->supportsTime()}datetime{else}date{/if}"
	id="{$field->getPrefixedId()}_from"
	name="{$field->getPrefixedId()}[from]"
	value="{$field->getFromValue()}"
	data-placeholder="{lang}wcf.date.period.start{/lang}"
	{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}
	{if $field->isAutofocused()} autofocus{/if}
	{if $field->isRequired()} required{/if}
	{if $field->isImmutable()} disabled{/if}
	{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}
>
<input
	type="{if $field->supportsTime()}datetime{else}date{/if}"
	id="{$field->getPrefixedId()}_to"
	name="{$field->getPrefixedId()}[to]"
	value="{$field->getToValue()}"
	data-placeholder="{lang}wcf.date.period.end{/lang}"
	{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}
	{if $field->isRequired()} required{/if}
	{if $field->isImmutable()} disabled{/if}
	{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}
>
