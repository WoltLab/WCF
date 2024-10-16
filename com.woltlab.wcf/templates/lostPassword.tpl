{include file='header' __disableAds=true}

<p class="info" role="status">{lang}wcf.user.lostPassword.description{/lang}</p>

{include file='formError'}

<form method="post" action="{link controller='LostPassword'}{/link}">
	<div class="section">
		<dl id="usernameDiv"{if $errorField == 'username'} class="formError"{/if}>
			<dt>
				<label for="usernameInput">{lang}wcf.user.username{/lang}</label>
			</dt>
			<dd>
				<input type="text" id="usernameInput" name="username" value="{$username}" class="medium">
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
		
		<dl id="emailDiv"{if $errorField == 'email'} class="formError"{/if}>
			<dt>
				<label for="emailInput">{lang}wcf.user.email{/lang}</label>
			</dt>
			<dd>
				<input type="email" id="emailInput" name="email" value="{$email}" class="medium">
				{if $errorField == 'email'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'notFound'}
							{lang}wcf.user.lostPassword.email.error.notFound{/lang}
						{else}
							{lang}wcf.user.email.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		{event name='fields'}
	</div>
	
	{event name='sections'}
	
	{include file='captcha' supportsAsyncCaptcha=true}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

<script data-relocate="true">
	$(function() {
		new WCF.User.Registration.LostPassword();
	});
</script>

{include file='footer' __disableAds=true}
