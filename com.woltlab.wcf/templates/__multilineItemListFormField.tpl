<ul class="scrollableCheckboxList" {*
    *}id="lineBreakSeparatedTextOption_{$field->getPrefixedId()}"{*
    *}{if $field->getValue()|empty} style="display: none"{/if}{*
*}>
	{foreach from=$field->getValue() item=value}
		<li data-value="{$value}">
			<button class="jsEditItem jsTooltip" type="button" title="{lang}wcf.global.button.edit{/lang}">
				{icon name='pencil'}
			</button>
			<button class="jsDeleteItem jsTooltip" type="button" title="{lang}wcf.global.button.delete{/lang}">
				{icon name='trash'}
			</button>
			<span>{$value}</span>
		</li>
	{/foreach}
</ul>

<script data-relocate="true">
	require(["WoltLabSuite/Core/Form/Builder/Field/Controller/MultilineItemList"], ({ MultilineItemListFormField }) => {
		{jsphrase name='wcf.acp.option.type.lineBreakSeparatedText.placeholder'}
		{jsphrase name='wcf.acp.option.type.lineBreakSeparatedText.clearList.confirmMessage'}
		{jsphrase name='wcf.global.button.save'}
		{jsphrase name='wcf.global.button.cancel'}
		{jsphrase name='wcf.global.button.edit'}
		WoltLabLanguage.registerPhrase("wcf.acp.option.type.lineBreakSeparatedText.error.duplicate", '{jslang __literal=true}wcf.acp.option.type.lineBreakSeparatedText.error.duplicate{/jslang}');

		new MultilineItemListFormField(document.getElementById('lineBreakSeparatedTextOption_{@$field->getPrefixedId()|encodeJS}'), {
			submitFieldName: '{@$field->getPrefixedId()|encodeJS}[]',
		});
	});
	{if $field->isFilterable()}
	require(["WoltLabSuite/Core/Ui/ItemList/Filter"], (UiItemListFilter) => {
		{jsphrase name='wcf.global.filter.button.visibility'}
		{jsphrase name='wcf.global.filter.button.clear'}
		{jsphrase name='wcf.global.filter.error.noMatches'}
		{jsphrase name='wcf.global.filter.placeholder'}
		{jsphrase name='wcf.global.filter.visibility.activeOnly'}
		{jsphrase name='wcf.global.filter.visibility.highlightActive'}
		{jsphrase name='wcf.global.filter.visibility.showAll'}

		new UiItemListFilter('lineBreakSeparatedTextOption_{@$field->getPrefixedId()|encodeJS}');
	});
	{/if}
</script>
