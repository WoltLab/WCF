{include file='header' __disableAds=true}

{include file='formError'}

<form method="post" action="{link controller='RegisterNewActivationCode'}{/link}">
	<div class="section">
		<dl{if $errorField == 'username'} class="formError"{/if}>
			<dt><label for="username">{lang}wcf.user.username{/lang}</label></dt>
			<dd>
				<input type="text" id="username" name="username" value="{$username}" required class="medium">
				{if $errorField == 'username'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'alreadyEnabled'}
							{lang}wcf.user.registerActivation.error.userAlreadyEnabled{/lang}
						{else}
							{lang}wcf.user.username.error.{@$errorType}{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl{if $errorField == 'password'} class="formError"{/if}>
			<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
			<dd>
				<input type="password" id="password" name="password" value="{$password}" required class="medium">
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
		
		<dl{if $errorField == 'email'} class="formError"{/if}>
			<dt><label for="email">{lang}wcf.user.email{/lang}</label></dt>
			<dd>
				<input type="email" id="email" name="email" value="{$email}" class="medium">
				{if $errorField == 'email'}
					<small class="innerError">
						{lang}wcf.user.email.error.{@$errorType}{/lang}
					</small>
				{/if}
				<small>{lang}wcf.user.registerNewActivationCode.email.description{/lang}</small>
			</dd>
		</dl>
		
		{event name='fields'}
	</div>
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{csrfToken}
	</div>
</form>

{include file='footer' __disableAds=true}
