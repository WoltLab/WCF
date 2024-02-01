{if $availableLanguages|count > 1}
	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Language/Input', 'WoltLabSuite/Core/Language/Text'], function(Language, LanguageInput, LanguageText) {
			Language.addObject({
				'wcf.global.button.disabledI18n': '{jslang}wcf.global.button.disabledI18n{/jslang}'
			});

			var availableLanguages = { {implode from=$availableLanguages key=languageID item=languageName}{@$languageID}: '{$languageName}'{/implode} };
			var values = { {implode from=$i18nValues[$elementIdentifier] key=languageID item=value}'{@$languageID}': '{$value}'{/implode} };
			
			var element = elById('{@$elementIdentifier}');
			var type = LanguageInput;
			if (element && element.nodeName === 'TEXTAREA' && element.classList.contains('wysiwygTextarea')) {
				type = LanguageText;
			}
			
			type['init']('{@$elementIdentifier}', values, availableLanguages, {if $forceSelection}true{else}false{/if});
		});
	</script>
{/if}
