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

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/loginL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.masterPassword.init{/lang}</h1>
	</hgroup>
</header>

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="post" action="index.php?form=MasterPasswordInit">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.masterPassword.init{/lang}</legend>
			
				<dl id="masterPasswordDiv"{if $errorField == 'masterPassword'} class="formError{/if}">
					<dt><label for="masterPassword">{lang}wcf.acp.masterPassword{/lang}</label></dt>
					<dd>
						<input type="password" id="masterPassword" name="masterPassword" value="{$masterPassword}" class="medium" />
						{if $errorField == 'masterPassword'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notSecure'}{lang}wcf.acp.masterPassword.error.notSecure{/lang}{/if}
							</small>
						{/if}
						<small id="masterPasswordHelpMessage">{lang}wcf.acp.masterPassword.init.description{/lang}</small>
					</dd>
				</dl>
				
				<dl{if $errorField == 'confirmMasterPassword'} class="formError"{/if}>
					<dt><label for="confirmMasterPassword">{lang}wcf.acp.masterPassword.confirm{/lang}</label></dt>
					<dd>
						<input type="password" id="confirmMasterPassword" name="confirmMasterPassword" value="{$confirmMasterPassword}" class="medium" />
						{if $errorField == 'confirmMasterPassword'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.error.empty{/lang}{/if}
								{if $errorType == 'notEqual'}{lang}wcf.acp.masterPassword.error.notEqual{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
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
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" name="submitButton" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SID_INPUT_TAG}
 		<input type="hidden" name="url" value="{$url}" />
 	</div>
</form>

{include file='footer'}
