<select
	id="{$field->getPrefixedId()}"
	name="{$field->getPrefixedId()}[]"
	{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}
	{if $field->isRequired()} required{/if}
	multiple
>
	{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
		<option
			value="{$__fieldNestedOption[value]}"
			{if $field->getValue() !== null && $__fieldNestedOption[value]|in_array:$field->getValue() && $__fieldNestedOption[isSelectable]} selected{/if}
			{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}
		>{@'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{@$__fieldNestedOption[label]}</option>
	{/foreach}
</select>
