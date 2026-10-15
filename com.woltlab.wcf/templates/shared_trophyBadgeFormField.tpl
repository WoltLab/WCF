<div class="badgeIconEditor" id="{$field->getPrefixedId()}_container">
	<span class="trophyIcon" style="color: {$field->getIconColor()}; background-color: {$field->getBadgeColor()}; --trophy-border-color: {$field->getBorderColor()}">
		{unsafe:$field->getIcon()->toHtml(64)}
	</span>
	{if !$field->isImmutable()}
		<div class="badgeIconEditorButtons">
			<button type="button" class="button small" data-value="icon">{lang}wcf.global.fontAwesome.selectIcon{/lang}</button>
			<button type="button" class="button small" data-value="color">{lang}wcf.style.colorPicker.changeColor{/lang}</button>
			<button type="button" class="button small" data-value="background-color">{lang}wcf.style.colorPicker.changeBackgroundColor{/lang}</button>
			<button type="button" class="button small" data-value="border-color">{lang}wcf.style.colorPicker.changeBorderColor{/lang}</button>
		</div>
	{/if}
</div>

<input type="hidden" id="{$field->getPrefixedId()}_iconName" name="{$field->getPrefixedId()}[iconName]" value="{$field->getIconName()}">
<input type="hidden" id="{$field->getPrefixedId()}_iconColor" name="{$field->getPrefixedId()}[iconColor]" value="{$field->getIconColor()}">
<input type="hidden" id="{$field->getPrefixedId()}_badgeColor" name="{$field->getPrefixedId()}[badgeColor]" value="{$field->getBadgeColor()}">
<input type="hidden" id="{$field->getPrefixedId()}_borderColor" name="{$field->getPrefixedId()}[borderColor]" value="{$field->getBorderColor()}">

{if !$field->isImmutable()}
	{if $__trophyBadgeFormFieldIncludeJavaScript}
		{include file='shared_fontAwesomeJavaScript'}
		{include file='shared_colorPickerJavaScript'}
	{/if}

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Form/Builder/Field/Controller/TrophyBadge'], ({ setup }) => {
			setup('{unsafe:$field->getPrefixedId()|encodeJS}');
		});
	</script>
{/if}
