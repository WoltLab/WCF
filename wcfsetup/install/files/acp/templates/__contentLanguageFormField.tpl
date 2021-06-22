<noscript>
	<select name="{@$field->getPrefixedId()}" id="{@$field->getPrefixedId()}"{if $field->isImmutable()} disabled{/if}>
		{if !$field->isRequired()}
			<option>{lang}wcf.global.language.noSelection{/lang}</option>
		{/if}
		{foreach from=$field->getContentLanguages() item=contentLanguage}
			<option value="{@$contentLanguage->languageID}">{$contentLanguage}</option>
		{/foreach}
	</select>
</noscript>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Language/Chooser', 'Dom/Traverse', 'Dom/Util'], (LanguageChooser, DomTraverse, DomUtil) => {
		const languages = {
			{implode from=$field->getContentLanguages() item=contentLanguage}
				'{@$contentLanguage->languageID}': {
					iconPath: '{@$contentLanguage->getIconPath()|encodeJS}',
					languageName: '{@$contentLanguage|encodeJS}',
				}
			{/implode}
		};
		
		LanguageChooser.init(
			DomUtil.identify(DomTraverse.childByTag(document.getElementById('{@$field->getPrefixedId()}Container'), 'DD')),
			'{@$field->getPrefixedId()}',
			{if $field->getValue()}{@$field->getValue()}{else}0{/if},
			languages,
			undefined,
			{if !$field->isRequired()}true{else}false{/if}
		)
	});
</script>
