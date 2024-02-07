<ul class="scrollableCheckboxList" {*
	*}id="lineBreakSeparatedTextOption_{@$identifier}"{*
	*}{if $values|empty} style="display: none"{/if}{*
*}>
	{foreach from=$values item=value}
		<li data-value="{$value}">
			<button type="button" class="jsDeleteItem jsTooltip" title="{lang}wcf.global.button.delete{/lang}">
				{icon name='xmark'}
			</button>
			<span>{$value}</span>
		</li>
	{/foreach}
</ul>
<input type="hidden" name="values[{$option->optionName}]">

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Ui/ItemList/LineBreakSeparatedText'], (Language, { UiItemListLineBreakSeparatedText }) => {
		Language.addObject({
			'wcf.acp.option.type.lineBreakSeparatedText.placeholder': '{jslang}wcf.acp.option.type.lineBreakSeparatedText.placeholder{/jslang}',
			'wcf.acp.option.type.lineBreakSeparatedText.error.duplicate': '{jslang __literal=true}wcf.acp.option.type.lineBreakSeparatedText.error.duplicate{/jslang}',
			'wcf.acp.option.type.lineBreakSeparatedText.clearList.confirmMessage': '{jslang}wcf.acp.option.type.lineBreakSeparatedText.clearList.confirmMessage{/jslang}',
		});
		
		new UiItemListLineBreakSeparatedText(
			document.getElementById("lineBreakSeparatedTextOption_{@$identifier}")
		);
	});
</script>
