{if !$forceSelection|isset}{assign var=forceSelection value=false}{/if}
{if !$elementName|isset}{assign var=elementName value=$elementIdentifier}{/if}
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var $availableLanguages = { {implode from=$availableLanguages key=languageID item=languageName}{@$languageID}: '{$languageName}'{/implode} };
		var $values = { {implode from=$i18nValues[$elementName] key=languageID item=value}'{@$languageID}': '{$value}'{/implode} };
		new WCF.MultipleLanguageInput('{@$elementIdentifier}', {if $forceSelection}true{else}false{/if}, $values, $availableLanguages);
	});
	//]]>
</script>