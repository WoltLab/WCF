{include file='header' pageTitle='wcf.user.login' __isLogin=true}

<div id="login" style="display: none">
	<form method="post" action="{link controller='Login'}{/link}">
		{if !$errorField|empty && $errorField == 'cookie'}
			<p class="error">{lang}wcf.user.login.error.cookieRequired{/lang}</p>
		{else}
			{include file='formError'}
		{/if}
		
		<dl{if $errorField == 'username'} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd><input type="text" id="username" name="username" value="{$username}" class="long">
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
			<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
			<dd><input type="password" id="password" name="password" value="" class="long">
				{if $errorField == 'password'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else}
							{lang}wcf.user.password.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
			
		{include file='captcha'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			<input type="hidden" name="url" value="{$url}">
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
</div>

<script data-relocate="true">
	document.addEventListener('DOMContentLoaded', function() {
		require(['Ui/Dialog'], function (UiDialog) {
			UiDialog.openStatic('login', null, {
				closable: false,
				title: '{@$pageTitle|language}',
				onShow: function() {
					if (elById('username').value === '' || '{$errorField}' === 'username') {
						elById('username').focus();
					}
					else {
						elById('password').focus();
					}
				}
			});
		});
	});
</script>

{include file='footer'}
