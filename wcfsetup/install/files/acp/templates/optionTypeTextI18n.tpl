<script type="text/javascript">
	//<![CDATA[
	$(function() {
		var $availableLanguages = { {implode from=$availableLanguages key=languageID item=languageName}{@$languageID}: '{$languageName}'{/implode} };
		var $optionValues = { {implode from=$i18nValues[$option->optionName] key=languageID item=value}'{@$languageID}': '{$value}'{/implode} };
		new WCF.MultipleLanguageInput('{$option->optionName}', false, $optionValues, $availableLanguages);
	});
	//]]>
</script>
<input type="{@$inputType}" id="{$option->optionName}" name="{$option->optionName}" value="{$i18nPlainValues[$option->optionName]}" class="medium" />
