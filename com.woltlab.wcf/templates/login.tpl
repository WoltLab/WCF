{if $__userAuthConfig->canRegister}
	{capture assign='contentDescription'}{lang}wcf.user.login.noAccount{/lang}{/capture}
{/if}

{include file='authFlowHeader'}

{if $forceLoginRedirect}
	<woltlab-core-notice type="info">{lang}wcf.user.login.forceLogin{/lang}</woltlab-core-notice>
{/if}

{if !$errorField|empty && $errorField == 'cookie'}
	<woltlab-core-notice type="error">{lang}wcf.user.login.error.cookieRequired{/lang}</woltlab-core-notice>
{else}
	{include file='shared_formError'}
{/if}

<form id="loginForm" method="post" action="{$loginController}">
	<dl{if $errorField == 'username'} class="formError"{/if}>
		<dt>
			<label for="username">{lang}wcf.user.usernameOrEmail{/lang}</label> <span class="formFieldRequired">*</span>
		</dt>
		<dd>
			<input type="text" id="username" name="username" value="{$username}" required autofocus class="long" autocomplete="username">
			{if $errorField == 'username'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.user.username.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
	
	<dl{if $errorField == 'password'} class="formError"{/if}>
		<dt>
			<label for="password">{lang}wcf.user.password{/lang}</label> <span class="formFieldRequired">*</span>
		</dt>
		<dd>
			<input type="password" id="password" name="password" value="{$password}" required class="long" autocomplete="current-password">
			{if $errorField == 'password'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.user.password.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
			{if $__userAuthConfig->canChangePassword}
				<small><a href="{link controller='LostPassword'}{/link}">{lang}wcf.user.lostPassword{/lang}</a></small>
			{/if}
		</dd>
	</dl>
	
	{event name='fields'}

	{include file='shared_captcha' supportsAsyncCaptcha=true}

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.user.button.login{/lang}" accesskey="s">
		{csrfToken}
	</div>

	{include file='thirdPartySsoButtons'}
</form>

<p class="formFieldRequiredNotice">
	<span class="formFieldRequired">*</span>
	{lang}wcf.global.form.required{/lang}
</p>

{include file='authFlowFooter'}
