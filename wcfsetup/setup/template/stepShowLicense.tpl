{include file='header'}

<h2>{lang}wcf.global.license{/lang}</h2>

<p>{lang}wcf.global.license.description{/lang}</p>

{if $missingAcception|isset}
<p class="error">
	{lang}wcf.global.license.missingAcception{/lang}
</p>
{/if}

<form method="post" action="install.php">
	<div>
		<textarea rows="20" cols="40" style="width: 100%" readonly="readonly">{$license}</textarea>
		<p><label{if $missingAcception|isset} class="errorField"{/if}><input type="checkbox" name="accepted" value="1" /> {lang}wcf.global.license.accept.description{/lang}</label></p>
	</div>
	<hr />
	<div class="nextButton">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.next{/lang}" />
		<input type="hidden" name="send" value="1" />
		<input type="hidden" name="step" value="{@$nextStep}" />
		<input type="hidden" name="tmpFilePrefix" value="{@$tmpFilePrefix}" />
		<input type="hidden" name="languageCode" value="{@$languageCode}" />
		<input type="hidden" name="dev" value="{@$developerMode}" />
	</div>
</form>

{include file='footer'}