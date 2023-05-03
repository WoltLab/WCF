<select
	id="{$field->getPrefixedId()}"
	name="{$field->getPrefixedId()}"
	{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}
	{if $field->isRequired()} required{/if}
>
	<option value="">{lang}wcf.global.noSelection{/lang}</option>
	{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
		<option
			value="{$__fieldNestedOption[value]}"
			{if $field->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]} selected{/if}
			{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}
		>{@'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{@$__fieldNestedOption[label]}</option>
	{/foreach}
</select>
