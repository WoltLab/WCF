{include file='header'}

<header class="wcf-container wcf-mainHeading">
	<img src="{@$__wcf->getPath()}icon/update1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.packageUpdate.auth{/lang}</h1>
	</hgroup>
</header>

{if $errorField != ''}
	<p class="wcf-error">{lang}wcf.global.form.error{/lang}</p>
{/if}

<form method="{$requestMethod}" action="index.php{if $getParameters}?{@$getParameters}{/if}">
	<div class="wcf-border wcf-content">
		
		<fieldset>
			<legend>{lang}wcf.acp.packageUpdate.auth.data{/lang}</legend>
			
			<dl>
				<dt>{lang}wcf.acp.packageUpdate.auth.url{/lang}</dt>
				<dd>{$url}</dd>
			</dl>
			{if $realm}
				<dl>
					<dt>{lang}wcf.acp.packageUpdate.auth.realm{/lang}</dt>
					<dd>{$realm}</dd>
				</dl>
			{/if}
			{if $message}
				<dl>
					<dt>{lang}wcf.acp.packageUpdate.auth.message{/lang}</dt>
					<dd>{@$message}</dd>
				</dl>
			{/if}
		</fieldset>
		
		<fieldset>
			<legend>{lang}wcf.acp.packageUpdate.auth.input{/lang}</legend>
			
			<dl{if $errorField == 'loginPassword'} class="wcf-formError"{/if}>
				<dt><label for="loginUsername">{lang}wcf.acp.packageUpdate.auth.loginUsername{/lang}</label></dt>
				<dd>
					<input type="text" id="loginUsername" name="loginUsername" value="{$loginUsername}" class="long" />
					<small>{lang}wcf.acp.packageUpdate.auth.loginUsername.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'loginPassword'} class="wcf-formError"{/if}>
				<dt><label for="loginPassword">{lang}wcf.acp.packageUpdate.auth.loginPassword{/lang}</label></dt>
				<dd>
					<input type="password" id="loginPassword" name="loginPassword" value="{$loginPassword}" class="medium" />
					{if $errorField == 'loginPassword'}
						<small class="wcf-innerError">
							{if $errorType == 'invalid'}{lang}wcf.acp.packageUpdate.auth.error{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.acp.packageUpdate.auth.loginPassword.description{/lang}</small>
				</dd>
			</dl>
			
			<dl>
				<dt>
					<label><input type="checkbox" id="saveAuthData" name="saveAuthData" value="1" {if $saveAuthData == 1}checked="checked" {/if}/> {lang}wcf.acp.packageUpdate.auth.save{/lang}</label>
				</dt>
				<dd><small>{lang}wcf.acp.packageUpdate.auth.save.description{/lang}</small></dd>
			</dl>
		</fieldset>
			
	</div>
	
	<div class="wcf-formSubmit">
		<input type="reset" value="{lang}wcf.global.button.reset{/lang}" accesskey="r" />
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="form" value="PackageUpdateAuth" />
		{@SID_INPUT_TAG}
		<input type="hidden" name="id" value="{@$packageUpdateServerID}" />
		{@$postParameters}
	</div>
</form>

{include file='footer'}
