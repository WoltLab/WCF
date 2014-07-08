<fieldset id="settings_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" class="container containerPadding">
	<dl class="wide">
		{if $__wcf->getSession()->getPermission('user.message.canUseBBCodes')}
			<dt></dt>
			<dd>
				<label><input id="preParse_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" name="preParse" type="checkbox" value="1"{if !$enableBBCodes|isset || $enableBBCodes} checked="checked"{/if} data-submit-empty="0" /> {lang}wcf.message.settings.preParse{/lang}</label>
				<small>{lang}wcf.message.settings.preParse.description{/lang}</small>
			</dd>
		{/if}
		{if MODULE_SMILEY && $__wcf->getSession()->getPermission('user.message.canUseSmilies')}
			<dt></dt>
			<dd>
				<label><input id="enableSmilies_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" name="enableSmilies" type="checkbox" value="1"{if !$enableSmilies|isset || $enableSmilies} checked="checked"{/if} data-submit-empty="0" /> {lang}wcf.message.settings.enableSmilies{/lang}</label>
				<small>{lang}wcf.message.settings.enableSmilies.description{/lang}</small>
			</dd>
		{/if}
		{if $__wcf->getSession()->getPermission('user.message.canUseBBCodes')}
			<dt></dt>
			<dd>
				<label><input id="enableBBCodes_{if $wysiwygSelector|isset}{$wysiwygSelector}{else}text{/if}" name="enableBBCodes" type="checkbox" value="1"{if !$enableBBCodes|isset || $enableBBCodes} checked="checked"{/if} data-submit-empty="0" /> {lang}wcf.message.settings.enableBBCodes{/lang}</label>
				<small>{lang}wcf.message.settings.enableBBCodes.description{/lang}</small>
			</dd>
		{/if}
		
		{event name='settings'}
	</dl>
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
