{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		$('#setPassword').click(function() {
			$('input[type="password"]').val($.proxy(function(index, element) {
				return $(this).text();
			}, this));
		});
	});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/loginL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.masterPassword.init{/lang}</h2>
	</div>
</div>


{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=MasterPasswordInit">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.masterPassword.init{/lang}</legend>
			
				<div class="formElement{if $errorField == 'masterPassword'} formError{/if}" id="masterPasswordDiv">
					<div class="formFieldLabel">
						<label for="masterPassword">{lang}wcf.acp.masterPassword{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" id="masterPassword" name="masterPassword" value="{$masterPassword}" />
						{if $errorField == 'masterPassword'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notSecure'}{lang}wcf.acp.masterPassword.error.notSecure{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="masterPasswordHelpMessage">
						<p>{lang}wcf.acp.masterPassword.init.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('masterPassword');
				//]]></script>
				
				<div class="formElement{if $errorField == 'confirmMasterPassword'} formError{/if}">
					<div class="formFieldLabel">
						<label for="confirmMasterPassword">{lang}wcf.acp.masterPassword.confirm{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" id="confirmMasterPassword" name="confirmMasterPassword" value="{$confirmMasterPassword}" />
						{if $errorField == 'confirmMasterPassword'}
							<p class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notEqual'}{lang}wcf.acp.masterPassword.error.notEqual{/lang}{/if}
							</p>
						{/if}
					</div>
				</div>
				
				<div class="formElement">
					<div class="formFieldLabel">
						<label for="exampleMasterPassword">{lang}wcf.acp.masterPassword.example{/lang}</label>
					</div>
					<div class="formField">
						<p><a href="#" id="setPassword" title="{lang}wcf.acp.masterPassword.example.set{/lang}">{@$exampleMasterPassword}</a></p>
						<input type="hidden" id="exampleMasterPassword" name="exampleMasterPassword" value="{@$exampleMasterPassword}" />
					</div>
				</div>
			</fieldset>
			
			{if $additionalFields|isset}{@$additionalFields}{/if}
		</div>
	</div>

	<div class="formSubmit">
		<input type="submit" accesskey="s" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="url" value="{$url}" />
 	</div>
</form>

{include file='footer'}