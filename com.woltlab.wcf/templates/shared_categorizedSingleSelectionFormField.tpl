<script data-relocate="true">
	{jsphrase name='wcf.global.filter.button.visibility'}
	{jsphrase name='wcf.global.filter.button.clear'}
	{jsphrase name='wcf.global.filter.error.noMatches'}
	{jsphrase name='wcf.global.filter.placeholder'}
	{jsphrase name='wcf.global.filter.visibility.activeOnly'}
	{jsphrase name='wcf.global.filter.visibility.highlightActive'}
	{jsphrase name='wcf.global.filter.visibility.showAll'}

	require(['WoltLabSuite/Core/Component/ItemList/Categorized'], ({ CategorizedItemList  }) => {
		new CategorizedItemList('{unsafe:$field->getPrefixedId()|encodeJS}_list');
	});
</script>

<div class="itemListFilter" id="{$field->getPrefixedId()}_list">
	<div class="inputAddon">
		<input type="text" class="long" placeholder="{lang}wcf.global.filter.placeholder{/lang}">
		<button type="button" class="button clearButton inputSuffix disabled jsTooltip" title="{lang}wcf.global.filter.button.clear{/lang}">{icon name="xmark" solid=true}</button>
	</div>
	<ul class="scrollableCheckboxList">
		{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
			<li
				{if $__fieldNestedOption[depth] > 0} style="padding-left: {$__fieldNestedOption[depth]*20}px"{/if}
				{if !$__fieldNestedOption[isSelectable]} class="scrollableCheckboxList__category"{/if}
			>
				{if !$__fieldNestedOption[isSelectable]}
					<span class="scrollableCheckboxList__category__label">{unsafe:$__fieldNestedOption[label]}</span>
				{else}
					<label>
						<input {*
							*}type="radio" {*
							*}name="{$field->getPrefixedId()}" {*
							*}value="{$__fieldNestedOption[value]}"{*
							*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
							*}{if $field->getValue() == $__fieldNestedOption[value] && $__fieldNestedOption[isSelectable]} checked{/if}{*
							*}{if $field->isImmutable()} disabled{/if}{*
						*}> <span>{unsafe:$__fieldNestedOption[label]}</span>
					</label>
				{/if}
			</li>
		{/foreach}
	</ul>
</div>
