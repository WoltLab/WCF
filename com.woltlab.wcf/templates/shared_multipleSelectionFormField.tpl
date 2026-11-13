{if !$field->isImmutable() && $field->getOptions()|count > 1}
	<div class="scrollableCheckboxList__selectAll">
		<label>
			<input type="checkbox" id="{$field->getPrefixedId()}_list_selectAll">
			{lang}wcf.global.form.selectAll{/lang}
		</label>
	</div>
{/if}

{if $field->isFilterable() || (!$field->isImmutable() && $field->getOptions()|count > 1)}
	<script data-relocate="true">
		require([
			'WoltLabSuite/Core/Ui/ItemList/Filter',
			'WoltLabSuite/Core/Component/ScrollableCheckboxList/SelectAll',
		], (UiItemListFilter, { setup }) => {
			{if $field->isFilterable()}
				{jsphrase name='wcf.global.filter.button.visibility'}
				{jsphrase name='wcf.global.filter.button.clear'}
				{jsphrase name='wcf.global.filter.error.noMatches'}
				{jsphrase name='wcf.global.filter.placeholder'}
				{jsphrase name='wcf.global.filter.visibility.activeOnly'}
				{jsphrase name='wcf.global.filter.visibility.highlightActive'}
				{jsphrase name='wcf.global.filter.visibility.showAll'}
				
				new UiItemListFilter('{unsafe:$field->getPrefixedId()|encodeJS}_list');
			{/if}
			
			{if !$field->isImmutable() && $field->getOptions()|count > 1}
				setup('{unsafe:$field->getPrefixedId()|encodeJS}_list');
			{/if}
		});
	</script>
{/if}

<ul class="scrollableCheckboxList" id="{$field->getPrefixedId()}_list">
	{foreach from=$field->getNestedOptions() item=__fieldNestedOption}
		<li data-depth="{$__fieldNestedOption[depth]}"{if $__fieldNestedOption[depth] > 0} style="padding-left: {$__fieldNestedOption[depth]*20}px"{/if}>
			<label>
				<input {*
					*}type="checkbox" {*
					*}name="{$field->getPrefixedId()}[]" {*
					*}value="{$__fieldNestedOption[value]}"{*
					*}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
					*}{if $field->getValue() !== null && $__fieldNestedOption[value]|in_array:$field->getValue() && $__fieldNestedOption[isSelectable]} checked{/if}{*
					*}{if $field->isImmutable() || !$__fieldNestedOption[isSelectable]} disabled{/if}{*
					*}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
				*}>
				{unsafe:$__fieldNestedOption[label]}
			</label>
		</li>
	{/foreach}
</ul>
