{if $availableContentLanguages|count}
	<dl{if $errorField == 'languageID'} class="formError"{/if}>
		<dt>{lang}wcf.user.language{/lang}</dt>
		<dd id="languageIDContainer">
			<noscript>
				<select name="languageID" id="languageID">
					{foreach from=$availableContentLanguages item=contentLanguage}
						<option value="{@$contentLanguage->languageID}">{$contentLanguage}</option>
					{/foreach}
				</select>
			</noscript>
		</dd>
	</dl>
	
	<script data-relocate="true">
		require(['WoltLab/WCF/Language/Chooser'], function(LanguageChooser) {
			var languages = {
				{implode from=$availableContentLanguages item=__language}
					'{@$__language->languageID}': {
						iconPath: '{@$__language->getIconPath()|encodeJS}',
						languageName: '{@$__language|encodeJS}'
					}
				{/implode}
			};
			
			LanguageChooser.init('languageIDContainer', 'languageID', {$languageID}, languages)
		});
	</script>
{/if}