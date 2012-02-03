{include file='header'}

<hgroup class="wcf-subHeading">
	<h1>{lang}wcf.global.license{/lang}</h1>
	<h2>{lang}wcf.global.license.description{/lang}</h2>
</hgroup>

{if $missingAcception|isset}
	<p class="wcf-error">{lang}wcf.global.license.missingAcception{/lang}</p>
{/if}

<form method="post" action="install.php">
	<div>
		<textarea rows="20" cols="40" readonly="readonly" id="license">{$license}</textarea>
		<p><label{if $missingAcception|isset} class="wcf-formError"{/if}><input type="checkbox" name="accepted" value="1" /> {lang}wcf.global.license.accept.description{/lang}</label></p>
	</div>
	
	<div class="wcf-formSubmit">
		<input type="submit" value="{lang}wcf.global.button.next{/lang}" accesskey="s" />
		<input type="hidden" name="send" value="1" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	document.getElementById('license').focus();
	//]]>
</script>

{include file='footer'}
