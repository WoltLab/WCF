<noscript>
	<select name="{$field->getPrefixedId()}" id="{$field->getPrefixedId()}"{if $field->isImmutable()} disabled{/if}>
		{if !$field->isRequired()}
			<option>{lang}wcf.global.language.noSelection{/lang}</option>
		{/if}
		{foreach from=$field->getContentLanguages() item=contentLanguage}
			<option value="{$contentLanguage->languageID}">{$contentLanguage}</option>
		{/foreach}
	</select>
</noscript>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Language/Chooser', 'Dom/Util'], function(LanguageChooser, DomUtil) {
		var languages = {
			{implode from=$field->getContentLanguages() item=contentLanguage}
				'{$contentLanguage->languageID}': {
					bcp47: '{unsafe:$contentLanguage->getBcp47()|encodeJS}',
					iconPath: '{unsafe:$contentLanguage->getIconPath()|encodeJS}',
					languageName: '{unsafe:$contentLanguage|encodeJS}'
				}
			{/implode}
		};
		
		LanguageChooser.init(
			DomUtil.identify(elById('{unsafe:$field->getPrefixedId()|encodeJS}Container').querySelector(':scope > .formField__input')),
			'{unsafe:$field->getPrefixedId()|encodeJS}',
			{if $field->getValue()}{$field->getValue()}{else}0{/if},
			languages,
			undefined,
			{if !$field->isRequired()}true{else}false{/if}
		)
	});
</script>
