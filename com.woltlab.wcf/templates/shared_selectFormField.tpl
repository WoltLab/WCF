<select
	id="{$field->getPrefixedId()}"
	name="{$field->getPrefixedId()}"
	{if !$field->getFieldClasses()|empty || $field->isImmutable()} class="{if $field->isImmutable()}disabled {/if}{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}
	{if $field->isImmutable()}
		tabindex="-1"
	{elseif $field->isRequired()}
		required
	{/if}
>
	{if !$field->hasDefaultValue()}
		<option value="">{lang}wcf.global.noSelection{/lang}</option>
	{/if}
	{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
		<option
			value="{$__fieldNestedOption[value]}"
			{if $field->getValue() !== null && $field->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]} selected{/if}
			{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}
		>{unsafe:'&nbsp;'|str_repeat:$__fieldNestedOption[depth] * 4}{unsafe:$__fieldNestedOption[label]}</option>
	{/foreach}
</select>
