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
		
		<fieldset>
			<legend>{lang}wcf.acp.masterPassword.enter{/lang}</legend>
		
			<dl id="masterPasswordDiv"{if $errorField == 'masterPassword'} class="formError"{/if}>
				<dt><label for="masterPassword">{lang}wcf.acp.masterPassword{/lang}</label></dt>
				<dd>
					<input type="password" id="masterPassword" name="masterPassword" value="{$masterPassword}" class="medium" />
					{if $errorField == 'masterPassword'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
							{if $errorType == 'invalid'}{lang}wcf.acp.masterPassword.error.invalid{/lang}{/if}
						</small>
					{/if}
					<small id="masterPasswordHelpMessage">{lang}wcf.acp.masterPassword.enter.description{/lang}</small>
				</dd>
			</dl>
		</fieldset>
		
		{if $additionalFields|isset}{@$additionalFields}{/if}
	</div>

	<div class="formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
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
