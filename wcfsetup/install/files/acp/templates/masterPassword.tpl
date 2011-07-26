{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/loginL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.masterPassword.enter{/lang}</h1>
	</hgroup>
</header>

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=MasterPassword">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.masterPassword.enter{/lang}</legend>
			
				<div class="formElement{if $errorField == 'masterPassword'} formError{/if}" id="masterPasswordDiv">
					<div class="formFieldLabel">
						<label for="masterPassword">{lang}wcf.acp.masterPassword{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" id="masterPassword" name="masterPassword" value="{$masterPassword}" />
						{if $errorField == 'masterPassword'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'invalid'}{lang}wcf.acp.masterPassword.error.invalid{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="masterPasswordHelpMessage">
						<p>{lang}wcf.acp.masterPassword.enter.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('masterPassword');
				//]]></script>
				
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="submit" accesskey="s" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="url" value="{$url}" />
 	</div>
</form>

<script type="text/javascript">
	//<![CDATA[
	document.observe("dom:loaded", function() {
		$('masterPassword').focus();
	});
	//]]>
</script>

{include file='footer'}
