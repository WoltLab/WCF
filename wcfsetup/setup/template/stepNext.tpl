{include file='header'}

<h2>{lang}wcf.global.next{/lang}</h2>

<p>{lang}wcf.global.next.description{/lang}</p>

<hr />

<form method="post" action="install.php?step={@$nextStep}">
	<div class="nextButton">
		<input type="submit" name="nextButton" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="wcfDir" value="{$wcfDir}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
		{foreach from=$selectedLanguages item=language}
			<input type="hidden" name="selectedLanguages[]" value="{$language}" />
		{/foreach}
	</div>
</form>


<script type="text/javascript">
	//<![CDATA[
	window.onload = function() {
		document.forms[0].nextButton.disabled = true;
		document.forms[0].submit();
	}
	//]]>
</script>

{include file='footer'}