{if !$__languageChooserPrefix|isset}{assign var='__languageChooserPrefix' value=''}{/if}
{if !$label|isset}{assign var='label' value='wcf.user.language'}{/if}

{if $languages|count}
	<dl{if $errorField|isset && $errorField == $__languageChooserPrefix|concat:'languageID'} class="formError"{/if}>
		<dt>{lang}{$label}{/lang}</dt>
		<dd id="{@$__languageChooserPrefix}languageIDContainer">
			<noscript>
				<select name="{@$__languageChooserPrefix}languageID" id="{@$__languageChooserPrefix}languageID">
					{foreach from=$languages item=_language}
						<option value="{$_language->languageID}">{$_language}</option>
					{/foreach}
				</select>
			</noscript>
		</dd>
	</dl>
	
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Language/Chooser'], function(LanguageChooser) {
			var languages = {
				{implode from=$languages item=_language}
					'{@$_language->languageID}': {
						iconPath: '{@$_language->getIconPath()|encodeJS}',
						languageName: '{@$_language|encodeJS}'
					}
				{/implode}
			};
			
			LanguageChooser.init('{@$__languageChooserPrefix}languageIDContainer', '{@$__languageChooserPrefix}languageID', {$languageID}, languages)
		});
	</script>
{/if}
