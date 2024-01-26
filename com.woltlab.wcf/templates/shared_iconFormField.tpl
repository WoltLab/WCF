<span id="{$field->getPrefixedId()}_icon">
	{if $field->getIcon()}
		{@$field->getIcon()->toHtml(64)}
	{/if}
</span>
{if !$field->isImmutable()}
	<button type="button" class="button small" id="{$field->getPrefixedId()}_openIconDialog">{lang}wcf.global.button.edit{/lang}</button>
	<button type="button" class="button small" id="{$field->getPrefixedId()}_removeIcon"{if !$field->getIcon()} hidden{/if}>{lang}wcf.global.button.delete{/lang}</button>
{/if}
<input type="hidden" id="{$field->getPrefixedId()}" name="{$field->getPrefixedId()}" value="{$field->getValue()}">

{if !$field->isImmutable()}
	{if $__iconFormFieldIncludeJavaScript}
		{include file='shared_fontAwesomeJavaScript'}
	{/if}
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Style/FontAwesome'], (UiStyleFontAwesome) => {
			const iconContainer = document.getElementById('{@$field->getPrefixedId()|encodeJS}_icon');
			const input = document.getElementById('{@$field->getPrefixedId()|encodeJS}');
			const buttonRemoveIcon = document.getElementById('{@$field->getPrefixedId()|encodeJS}_removeIcon');
			
			const callback = (iconName, forceSolid) => {
				input.value = `${ iconName };${ forceSolid }`;

				let icon = iconContainer.querySelector("fa-icon");
				if (icon) {
					icon.setIcon(iconName, forceSolid);
				} else {
					icon = document.createElement("fa-icon");
					icon.size = 64;
					icon.setIcon(iconName, forceSolid);
					iconContainer.append(icon);
				}

				buttonRemoveIcon.hidden = false;
			};
			
			const button = document.getElementById('{@$field->getPrefixedId()|encodeJS}_openIconDialog');
			button.addEventListener('click', () => UiStyleFontAwesome.open(callback));
			buttonRemoveIcon.addEventListener("click", () => {
				input.value = "";
				iconContainer.querySelector("fa-icon")?.remove();

				buttonRemoveIcon.hidden = true;
			});
		});
	</script>
{/if}
