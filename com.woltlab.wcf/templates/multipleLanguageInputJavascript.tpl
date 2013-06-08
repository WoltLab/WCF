{if !$forceSelection|isset}{assign var=forceSelection value=false}{/if}
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var $availableLanguages = { {implode from=$availableLanguages key=languageID item=languageName}{@$languageID}: '{$languageName}'{/implode} };
		var $values = { {implode from=$i18nValues[$elementIdentifier] key=languageID item=value}'{@$languageID}': '{$value}'{/implode} };
		new WCF.MultipleLanguageInput('{@$elementIdentifier}', {if $forceSelection}true{else}false{/if}, $values, $availableLanguages);
	});
	//]]>
</script>