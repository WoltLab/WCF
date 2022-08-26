<span id="{@$field->getPrefixedId()}_icon">
	{if $field->getIcon()}
		{@$field->getIcon()->toHtml(64)}
	{/if}
</span>
{if !$field->isImmutable()}
	<button class="button small" id="{@$field->getPrefixedId()}_openIconDialog">{lang}wcf.global.button.edit{/lang}</button>
{/if}
<input type="hidden" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="{$field->getValue()}">

{if !$field->isImmutable()}
	{if $__iconFormFieldIncludeJavaScript}
		{include file='fontAwesomeJavaScript'}
	{/if}
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Style/FontAwesome'], (UiStyleFontAwesome) => {
			const iconContainer = document.getElementById('{@$field->getPrefixedId()}_icon');
			const input = document.getElementById('{@$field->getPrefixedId()}');
			
			const callback = (iconName, forceSolid) => {
				input.value = iconName;

				let icon = iconContainer.querySelector("fa-icon");
				if (icon) {
					icon.setIcon(iconName, forceSolid);
				} else {
					icon = document.createElement("fa-icon");
					icon.size = 64;
					icon.setIcon(iconName, forceSolid);
					iconContainer.append(icon);
				}
			};
			
			const button = document.getElementById('{@$field->getPrefixedId()}_openIconDialog');
			button.addEventListener('click', () => UiStyleFontAwesome.open(callback));
		});
	</script>
{/if}
