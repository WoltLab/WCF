<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/Filter'], (UiItemListFilter) => {
		{jsphrase name='wcf.global.filter.button.visibility'}
		{jsphrase name='wcf.global.filter.button.clear'}
		{jsphrase name='wcf.global.filter.error.noMatches'}
		{jsphrase name='wcf.global.filter.placeholder'}
		{jsphrase name='wcf.global.filter.visibility.activeOnly'}
		{jsphrase name='wcf.global.filter.visibility.highlightActive'}
		{jsphrase name='wcf.global.filter.visibility.showAll'}

		new UiItemListFilter('{unsafe:$field->getPrefixedId()|encodeJS}_list');
	});
</script>

<ul class="scrollableCheckboxList" id="{$field->getPrefixedId()}_list">
	{if !$field->isRequired() && !$field->hasDefaultValue()}
		<li data-depth="0">
			<label>
				<input {*
					*}type="radio" {*
					*}name="{$field->getPrefixedId()}" {*
					*}value=""{*
					*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
					*}{if $field->getValue() === null} checked{/if}{*
					*}{if $field->isImmutable()} disabled{/if}{*
				*}>
				{lang}wcf.global.noSelection{/lang}
			</label>
		</li>
	{/if}
	{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
		<li data-depth="{$__fieldNestedOption[depth]}"{if $__fieldNestedOption[depth] > 0} style="padding-left: {$__fieldNestedOption[depth]*20}px"{/if}>
			<label>
				<input {*
					*}type="radio" {*
					*}name="{$field->getPrefixedId()}" {*
					*}value="{$__fieldNestedOption[value]}"{*
					*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
					*}{if $field->getValue() !== null && $field->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]} checked{/if}{*
					*}{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}{*
					*}{if $field->isRequired() && !$field->isImmutable()} required{/if}{*
				*}>
				{unsafe:$__fieldNestedOption[label]}
			</label>
		</li>
	{/foreach}
</ul>
