<span{if $field->getValue()} class="icon icon64 fa-{$field->getValue()}"{/if} id="{@$field->getPrefixedId()}_icon"></span>
{if !$field->isImmutable()}
	<button type="button" class="button small" id="{$field->getPrefixedId()}_openIconDialog">{lang}wcf.global.button.edit{/lang}</button>
	<button type="button" class="button small" id="{$field->getPrefixedId()}_removeIcon"{if !$field->getValue()} hidden{/if}>{lang}wcf.global.button.delete{/lang}</button>
{/if}
<input type="hidden" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="{$field->getValue()}">

{if !$field->isImmutable()}
	{if $__iconFormFieldIncludeJavaScript}
		{include file='fontAwesomeJavaScript'}
	{/if}
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Style/FontAwesome'], (UiStyleFontAwesome) => {
			const icon = document.getElementById('{@$field->getPrefixedId()|encodeJS}_icon');
			const input = document.getElementById('{@$field->getPrefixedId()|encodeJS}');
			const buttonRemoveIcon = document.getElementById('{@$field->getPrefixedId()|encodeJS}_removeIcon');
			
			const callback = (iconName) => {
				icon.className = 'icon icon64 fa-' + iconName;
				input.value = iconName;

				buttonRemoveIcon.hidden = false;
			};
			
			const button = document.getElementById('{@$field->getPrefixedId()|encodeJS}_openIconDialog');
			button.addEventListener('click', () => UiStyleFontAwesome.open(callback));
			buttonRemoveIcon.addEventListener("click", () => {
				input.value = "";
				icon.className = "";

				buttonRemoveIcon.hidden = true;
			});
		});
	</script>
{/if}
