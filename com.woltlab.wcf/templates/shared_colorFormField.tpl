{if $field->isImmutable()}
	<span class="colorPickerButton">
		<span{if $field->getValue()} style="background-color: {$field->getValue()}"{/if}></span>
	</span>
{else}
	<a href="#" class="colorPickerButton jsTooltip" id="{$field->getPrefixedId()}_colorPickerButton" title="{lang}wcf.style.colorPicker.button.changeColor{/lang}" data-store="{$field->getPrefixedId()}">
		<span{if $field->getValue()} style="background-color: {$field->getValue()}"{/if}></span>
	</a>
	<input type="hidden" {*
        *}id="{$field->getPrefixedId()}" {*
        *}name="{$field->getPrefixedId()}" {*
        *}value="{$field->getValue()}"{*
    *}>
	{include file='shared_colorPickerJavaScript'}

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Color/Picker'], (UiColorPicker) => {
			UiColorPicker.fromSelector("#{@$field->getPrefixedId()|encodeJS}_colorPickerButton");
		});
	</script>
{/if}
