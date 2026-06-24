<span id="{$field->getPrefixedId()}_icon">
	{if $field->getIcon()}
		{unsafe:$field->getIcon()->toHtml(64)}
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
			const iconContainer = document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}_icon');
			const input = document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}');
			const buttonRemoveIcon = document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}_removeIcon');

			const renderNativePreview = (iconName, forceSolid) => {
				let icon = iconContainer.querySelector("fa-icon");
				if (!icon || icon.parentElement !== iconContainer || iconContainer.childElementCount !== 1) {
					iconContainer.replaceChildren();

					icon = document.createElement("fa-icon");
					icon.size = 64;
					iconContainer.append(icon);
				}

				icon.setIcon(iconName, forceSolid);
			};

			const callback = (iconName, forceSolid, value, previewHtml) => {
				input.value = typeof value === 'string' ? value : `${ iconName };${ forceSolid }`;

				if (typeof previewHtml === 'string') {
					iconContainer.innerHTML = previewHtml;
				} else {
					renderNativePreview(iconName, forceSolid);
				}

				buttonRemoveIcon.hidden = false;
			};

			const button = document.getElementById('{unsafe:$field->getPrefixedId()|encodeJS}_openIconDialog');
			button.addEventListener('click', () => UiStyleFontAwesome.open(callback));
			buttonRemoveIcon.addEventListener("click", () => {
				input.value = "";
				iconContainer.replaceChildren();

				buttonRemoveIcon.hidden = true;
			});
		});
	</script>
{/if}
