<div class="section">
	<dl{if $errorType[username]|isset} class="formError"{/if}>
		<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
		<dd>
			<input type="text" name="username" value="{$username}" required autofocus class="long">
			{if $errorType[username]|isset}
				<small class="innerError">
					{if $errorType[username] == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.user.username.error.{$errorType[username]}{/lang}
					{/if}
				</small>
			{/if}
		</dd>
	</dl>
</div>

{include file='shared_captcha'}

<div class="formSubmit">
	<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" data-captcha-id="{@$captchaID}">
</div>
