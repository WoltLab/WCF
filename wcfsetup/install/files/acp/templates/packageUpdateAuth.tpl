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
				
				<div class="formElement{if $errorField == 'loginPassword'} formError{/if}" id="loginUsernameDiv">
					<div class="formFieldLabel">
						<label for="loginUsername">{lang}wcf.acp.packageUpdate.auth.loginUsername{/lang}</label>
					</div>
					<div class="formField">
						<input type="text" class="inputText" id="loginUsername" name="loginUsername" value="{$loginUsername}" />
					</div>
					<div class="formFieldDesc hidden" id="loginUsernameHelpMessage">
						<p>{lang}wcf.acp.packageUpdate.auth.loginUsername.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('loginUsername');
				//]]></script>
				
				<div class="formElement{if $errorField == 'loginPassword'} formError{/if}" id="loginPasswordDiv">
					<div class="formFieldLabel">
						<label for="loginPassword">{lang}wcf.acp.packageUpdate.auth.loginPassword{/lang}</label>
					</div>
					<div class="formField">
						<input type="password" class="inputText" id="loginPassword" name="loginPassword" value="{$loginPassword}" />
						{if $errorField == 'loginPassword'}
							<p class="innerError">
								{if $errorType == 'invalid'}{lang}wcf.acp.packageUpdate.auth.error{/lang}{/if}
							</p>
						{/if}
					</div>
					<div class="formFieldDesc hidden" id="loginPasswordHelpMessage">
						<p>{lang}wcf.acp.packageUpdate.auth.loginPassword.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('loginPassword');
				//]]></script>
				
				<div class="formElement" id="saveAuthDataDiv">
					<div class="formField">
						<label><input type="checkbox" id="saveAuthData" name="saveAuthData" value="1" {if $saveAuthData == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.auth.save{/lang}</label>
					</div>
					<div class="formFieldDesc hidden" id="saveAuthDataHelpMessage">
						<p>{lang}wcf.acp.packageUpdate.auth.save.description{/lang}</p>
					</div>
				</div>
				<script type="text/javascript">//<![CDATA[
					inlineHelp.register('saveAuthData');
				//]]></script>
			</fieldset>
			
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" accesskey="s" value="{lang}wcf.global.button.submit{/lang}" />
		<input type="reset" accesskey="r" value="{lang}wcf.global.button.reset{/lang}" />
		<input type="hidden" name="form" value="PackageUpdateAuth" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="packageUpdateServerID" value="{@$packageUpdateServerID}" />
		{@$postParameters}
	</div>
</form>

{include file='footer'}
