{if !$label|isset}{assign var='label' value='wcf.user.language'}{/if}

{if $languages|count}
	<dl{if $errorField|isset && $errorField == 'languageID'} class="formError"{/if}>
		<dt>{lang}{$label}{/lang}</dt>
		<dd id="languageIDContainer">
			<noscript>
				<select name="languageID" id="languageID">
					{foreach from=$languages item=__language}
						<option value="{@$__language->languageID}">{$__language}</option>
					{/foreach}
				</select>
			</noscript>
		</dd>
	</dl>
	
	<script data-relocate="true">
		require(['WoltLab/WCF/Language/Chooser'], function(LanguageChooser) {
			var languages = {
				{implode from=$languages item=__language}
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
