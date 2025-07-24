{if $field->isImmutable()}
	<span class="colorPickerButton">
		<span{if $field->getValue()} style="background-color: {$field->getValue()}"{/if}></span>
	</span>
{else}
	<button type="button" class="colorPickerButton jsTooltip" id="{$field->getPrefixedId()}_colorPickerButton" title="{lang}wcf.style.colorPicker.button.changeColor{/lang}" data-store="{$field->getPrefixedId()}">
		<span class="colorPickerButton__color"{if $field->getValue()} style="background-color: {$field->getValue()}"{/if}></span>
	</button>
	<input type="hidden" {*
		*}id="{$field->getPrefixedId()}" {*
		*}name="{$field->getPrefixedId()}" {*
		*}value="{$field->getValue()}"{*
	*}>
	{include file='shared_colorPickerJavaScript'}

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Color/Picker'], (UiColorPicker) => {
			UiColorPicker.fromSelector("#{unsafe:$field->getPrefixedId()|encodeJS}_colorPickerButton");
		});
	</script>
{/if}
