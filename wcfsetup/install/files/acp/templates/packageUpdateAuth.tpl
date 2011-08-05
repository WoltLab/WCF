{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/packageUpdateL.png" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.packageUpdate.auth{/lang}</h1>
	</hgroup>
</header>

{if $errorField != ''}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="{$requestMethod}" action="index.php{if $getParameters}?{@$getParameters}{/if}">
	<div class="border content">
		<div class="container-1">
			<fieldset>
				<legend>{lang}wcf.acp.packageUpdate.auth.data{/lang}</legend>
				
				<div class="formElement">
					<p class="formFieldLabel">{lang}wcf.acp.packageUpdate.auth.url{/lang}</p>
					<p class="formField">{$url}</p>
				</div>
				{if $realm}
					<div class="formElement">
						<p class="formFieldLabel">{lang}wcf.acp.packageUpdate.auth.realm{/lang}</p>
						<p class="formField">{$realm}</p>
					</div>
				{/if}
				{if $message}
					<div class="formElement">
						<p class="formFieldLabel">{lang}wcf.acp.packageUpdate.auth.message{/lang}</p>
						<p class="formField">{@$message}</p>
					</div>
				{/if}
			</fieldset>
			
			<fieldset>
				<legend>{lang}wcf.acp.packageUpdate.auth.input{/lang}</legend>
				
				<dl id="loginUsernameDiv" class="formElement{if $errorField == 'loginPassword'} formError{/if}">
					<dt><label for="loginUsername">{lang}wcf.acp.packageUpdate.auth.loginUsername{/lang}</label></dt>
					<dd>
						<input type="text" id="loginUsername" name="loginUsername" value="{$loginUsername}" class="long" />
						<small id="loginUsernameHelpMessage"><p>{lang}wcf.acp.packageUpdate.auth.loginUsername.description{/lang}</p></small>
					</dd>
				</dl>
				
				<dl id="loginPasswordDiv"{if $errorField == 'loginPassword'} class="formError"{/if}>
					<dt><label for="loginPassword">{lang}wcf.acp.packageUpdate.auth.loginPassword{/lang}</label></dt>
					<dd>
						<input type="password" id="loginPassword" name="loginPassword" value="{$loginPassword}" class="medium" />
						{if $errorField == 'loginPassword'}
							<small class="innerError">
								{if $errorType == 'invalid'}{lang}wcf.acp.packageUpdate.auth.error{/lang}{/if}
							</small>
						{/if}
						<small id="loginPasswordHelpMessage">{lang}wcf.acp.packageUpdate.auth.loginPassword.description{/lang}</small>
					</dd>
				</dl>
				
				<dl id="saveAuthDataDiv">
					<dt>
						<label><input type="checkbox" id="saveAuthData" name="saveAuthData" value="1" {if $saveAuthData == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.auth.save{/lang}</label>
					</dt>
					<dd id="saveAuthDataHelpMessage"><small>{lang}wcf.acp.packageUpdate.auth.save.description{/lang}</small></dd>
				</dl>
			</fieldset>
			
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="hidden" name="form" value="PackageUpdateAuth" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="packageUpdateServerID" value="{@$packageUpdateServerID}" />
		{@$postParameters}
	</div>
</form>

{include file='footer'}
