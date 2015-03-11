{hascontent}
	<fieldset id="settings_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" class="container containerPadding">
		{content}
			{event name='beforeSettings'}
			
			{capture assign='__messageFormSettingsInlineSettings'}{event name='settings'}{/capture}
			{assign var='__messageFormSettingsInlineSettings' value=$__messageFormSettingsInlineSettings|trim}
			
			{if $__messageFormSettingsInlineSettings}
				<dl class="condensed">
					{@$__messageFormSettingsInlineSettings}
				</dl>
			{/if}
			
			{event name='afterSettings'}
		{/content}
	</fieldset>
	<script data-relocate="true">
		$(function() {
			WCF.System.Event.addListener('com.woltlab.wcf.messageOptionsInline', 'submit_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}', function(parameters) {
				var $settings = $('#settings_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}');
				$settings.find('input, select, textarea').each(function(index, element) {
					var $element = $(element);
					var $value = $element.val();
					
					if ($element.getTagName() == 'input') {
						if (!$element.is(':checked')) {
							if ($element.prop('type') == 'checkbox' && $element.data('submitEmpty') !== undefined) {
								$value = $element.data('submitEmpty');
							}
							else {
								return true;
							}
						}
					}
					
					parameters[$element.prop('name')] = $value;
				});
			});
		});
	</script>
{/hascontent}
