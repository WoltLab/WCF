{include file='__formFieldHeader'}

<span{if $field->getValue()} class="icon icon64 fa-{$field->getValue()}"{/if} id="{@$field->getPrefixedId()}_icon"></span>
{if !$field->isImmutable()}
	<a href="#" class="button small" id="{@$field->getPrefixedId()}_openIconDialog">{lang}wcf.global.button.edit{/lang}</a>
{/if}
<input type="hidden" id="{@$field->getPrefixedId()}" name="{@$field->getPrefixedId()}" value="{$field->getValue()}">

{if !$field->isImmutable()}
	{if $__iconFormFieldIncludeJavaScript}
		{include file='fontAwesomeJavaScript'}
	{/if}
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Style/FontAwesome'], function(UiStyleFontAwesome) {
			var button = elById('{@$field->getPrefixedId()}_openIconDialog');
			var icon = elById('{@$field->getPrefixedId()}_icon');
			var input = elById('{@$field->getPrefixedId()}');
			
			var callback = function(iconName) {
				icon.className = 'icon icon64 fa-' + iconName;
				input.value = iconName;
			};
			
			button.addEventListener('click', function() {
				UiStyleFontAwesome.open(callback);
			});
		});
	</script>
{/if}

{include file='__formFieldFooter'}
