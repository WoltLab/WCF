{include file='header' pageTitle='wcf.acp.masterPassword.enter'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		$('#masterPassword').focus();
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.masterPassword.enter{/lang}</h1>
</header>

{include file='formError'}

<form method="post" action="{link controller='MasterPassword'}{/link}">
	<div class="section">
		<dl{if $errorField == 'masterPassword'} class="formError"{/if}>
			<dt><label for="masterPassword">{lang}wcf.acp.masterPassword{/lang}</label></dt>
			<dd>
				<input type="password" id="masterPassword" name="masterPassword" value="{$masterPassword}" class="medium">
				{if $errorField == 'masterPassword'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.acp.masterPassword.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
				<small>{lang}wcf.acp.masterPassword.enter.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='enterFields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="url" value="{$url}">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
