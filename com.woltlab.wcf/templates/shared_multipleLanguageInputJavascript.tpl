{if $availableLanguages|count > 1}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Language/Input', 'WoltLabSuite/Core/Language/Text'], function(LanguageInput, LanguageText) {
			{jsphrase name='wcf.global.button.disabledI18n'}

			var availableLanguages = { {implode from=$availableLanguages key=languageID item=languageName}{$languageID}: '{$languageName}'{/implode} };
			var values = { {implode from=$i18nValues[$elementIdentifier] key=languageID item=value}'{$languageID}': '{$value}'{/implode} };
			
			var element = elById('{unsafe:$elementIdentifier|encodeJS}');
			var type = LanguageInput;
			if (element && element.nodeName === 'TEXTAREA' && element.classList.contains('wysiwygTextarea')) {
				type = LanguageText;
			}
			
			type['init']('{unsafe:$elementIdentifier|encodeJS}', values, availableLanguages, {if $forceSelection}true{else}false{/if});
		});
	</script>
{/if}
