{if !REGISTER_DISABLED}
	{capture assign='contentDescription'}{lang}wcf.user.login.noAccount{/lang}{/capture}
{/if}

{include file='header' __disableAds=true}

{if $forceLoginRedirect}
	<woltlab-core-notice type="info">{lang}wcf.user.login.forceLogin{/lang}</woltlab-core-notice>
{/if}

{if !$errorField|empty && $errorField == 'cookie'}
	<woltlab-core-notice type="error">{lang}wcf.user.login.error.cookieRequired{/lang}</woltlab-core-notice>
{else}
	{include file='formError'}
{/if}

<form id="loginForm" method="post" action="{$loginController}">
	<dl{if $errorField == 'username'} class="formError"{/if}>
		<dt><label for="username">{lang}wcf.user.usernameOrEmail{/lang}</label></dt>
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
		<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
		<dd>
			<input type="password" id="password" name="password" value="{$password}" class="long" autocomplete="current-password">
			{if $errorField == 'password'}
				<small class="innerError">
					{if $errorType == 'empty'}
						{lang}wcf.global.form.error.empty{/lang}
					{else}
						{lang}wcf.user.password.error.{@$errorType}{/lang}
					{/if}
				</small>
			{/if}
			<small><a href="{link controller='LostPassword'}{/link}">{lang}wcf.user.lostPassword{/lang}</a></small>
		</dd>
	</dl>
	
	{event name='fields'}
	
	{include file='captcha' supportsAsyncCaptcha=true}

	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.user.button.login{/lang}" accesskey="s">
		{csrfToken}
	</div>

	{hascontent}
		<div class="thirdPartySsoButtons">
			<div class="thirdPartySsoButtons__separator">
				{lang}wcf.user.login.thirdPartySeparator{/lang}
			</div>

			<ul class="thirdPartySsoButtons__buttonList">
				{content}
					{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}
						<li id="facebookAuth" class="thirdPartyLogin">
							<a
								href="{link controller='FacebookAuth'}{/link}"
								class="button thirdPartyLoginButton facebookLoginButton"
								rel="nofollow"
							>{icon size=24 name='facebook' type='brand'} <span>{lang}wcf.user.3rdparty.facebook.login{/lang}</span></a>
						</li>
					{/if}
					
					{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}
						<li id="googleAuth" class="thirdPartyLogin">
							<a
								href="{link controller='GoogleAuth'}{/link}"
								class="button thirdPartyLoginButton googleLoginButton"
								rel="nofollow"
							>{icon size=24 name='google' type='brand'} <span>{lang}wcf.user.3rdparty.google.login{/lang}</span></a>
						</li>
					{/if}
				
					{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}
						<li id="twitterAuth" class="thirdPartyLogin">
							<a
								href="{link controller='TwitterAuth'}{/link}"
								class="button thirdPartyLoginButton twitterLoginButton"
								rel="nofollow"
							>{icon size=24 name='x-twitter' type='brand'} <span>{lang}wcf.user.3rdparty.twitter.login{/lang}</span></a>
						</li>
					{/if}
					
					{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
						<li id="githubAuth" class="thirdPartyLogin">
							<a
								href="{link controller='GithubAuth'}{/link}"
								class="button thirdPartyLoginButton githubLoginButton"
								rel="nofollow"
							>{icon size=24 name='github' type='brand'} <span>{lang}wcf.user.3rdparty.github.login{/lang}</span></a>
						</li>
					{/if}
					
					{event name='3rdpartyButtons'}
				{/content}
			</ul>
		</div>
	{/hascontent}
</form>

{include file='footer' __disableAds=true}
