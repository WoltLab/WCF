{if $availableContentLanguages|count}
	{if $availableContentLanguages|count == 1}
		{foreach from=$availableContentLanguages item=contentLanguage}
			<input type="hidden" name="languageID" value="{$contentLanguage->languageID}">
		{/foreach}
	{else}
		<dl{if $errorField == 'languageID'} class="formError"{/if}>
			<dt>{lang}wcf.user.language{/lang}</dt>
			<dd id="languageIDContainer">
				<noscript>
					<select name="languageID" id="languageID">
						{foreach from=$availableContentLanguages item=contentLanguage}
							<option value="{$contentLanguage->languageID}">{$contentLanguage}</option>
						{/foreach}
					</select>
				</noscript>
			</dd>
		</dl>
		
		<script data-relocate="true">
			require(['WoltLabSuite/Core/Language/Chooser'], function(LanguageChooser) {
				var languages = {
					{implode from=$availableContentLanguages item=_language}
						'{@$_language->languageID}': {
							iconPath: '{@$_language->getIconPath()|encodeJS}',
							languageName: '{@$_language|encodeJS}'
						}
					{/implode}
				};
				
				LanguageChooser.init('languageIDContainer', 'languageID', {$languageID}, languages)
			});
		</script>
	{/if}
{/if}
