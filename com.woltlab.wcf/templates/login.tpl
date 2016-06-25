{include file='header' __disableLoginLink=true __disableAds=true}

{if !$errorField|empty && $errorField == 'cookie'}
	<p class="error">{lang}wcf.user.login.error.cookieRequired{/lang}</p>
{else}
	{include file='formError'}
{/if}

<div id="loginForm" class="loginForm">
	<form method="post" action="{@$loginController}">
		<section class="section loginFormLogin">
			<h2 class="sectionTitle">{lang}wcf.user.login.login{/lang}</h2>
			
			<dl{if $errorField == 'username'} class="formError"{/if}>
				<dt><label for="username">{lang}wcf.user.usernameOrEmail{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" required class="long">
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
					<input type="password" id="password" name="password" value="{$password}" class="long">
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
			
			{if $supportsPersistentLogins}
				<dl>
					<dt></dt>
					<dd>
						<label for="useCookies"><input type="checkbox" id="useCookies" name="useCookies" value="1"{if $useCookies} checked{/if}> {lang}wcf.user.useCookies{/lang}</label>
					</dd>
				</dl>
			{/if}
			
			{event name='fields'}
			
			{include file='captcha'}
			
			<div class="userLoginButtons">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
				<input type="hidden" name="url" value="{$url}">
				{@SECURITY_TOKEN_INPUT_TAG}
			</div>
		</section>
		
		{if !REGISTER_DISABLED}
			<section class="section loginFormRegister">
				<h2 class="sectionTitle">{lang}wcf.user.login.register{/lang}</h2>
				
				<p>{lang}wcf.user.login.register.teaser{/lang}</p>
				
				<div class="userLoginButtons">
					<a href="{link controller='Register'}{/link}" class="button loginFormRegisterButton">{lang}wcf.user.login.register.registerNow{/lang}</a>
				</div>
			</section>
		{/if}
		
		{hascontent}
			<section class="section loginFormThirdPartyLogin">
				<h2 class="sectionTitle">{lang}wcf.user.login.3rdParty{/lang}</h2>
				
				<dl>
					<dt></dt>
					<dd>
						<ul class="buttonList smallButtons">
							{content}
								{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
									<li id="githubAuth" class="thirdPartyLogin">
										<a href="{link controller='GithubAuth'}{/link}" class="button thirdPartyLoginButton githubLoginButton"><span class="icon icon16 fa-github"></span> <span>{lang}wcf.user.3rdparty.github.login{/lang}</span></a>
									</li>
								{/if}
								
								{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}
									<li id="twitterAuth" class="thirdPartyLogin">
										<a href="{link controller='TwitterAuth'}{/link}" class="button thirdPartyLoginButton twitterLoginButton"><span class="icon icon16 fa-twitter"></span> <span>{lang}wcf.user.3rdparty.twitter.login{/lang}</span></a>
									</li>
								{/if}
								
								{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}
									<li id="facebookAuth" class="thirdPartyLogin">
										<a href="{link controller='FacebookAuth'}{/link}" class="button thirdPartyLoginButton facebookLoginButton"><span class="icon icon16 fa-facebook"></span> <span>{lang}wcf.user.3rdparty.facebook.login{/lang}</span></a>
									</li>
								{/if}
								
								{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}
									<li id="googleAuth" class="thirdPartyLogin">
										<a href="{link controller='GoogleAuth'}{/link}" class="button thirdPartyLoginButton googleLoginButton"><span class="icon icon16 fa-google-plus"></span> <span>{lang}wcf.user.3rdparty.google.login{/lang}</span></a>
									</li>
								{/if}
								
								{event name='3rdpartyButtons'}
							{/content}
						</ul>
					</dd>
				</dl>
			</section>
		{/hascontent}
	</form>
</div>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.User.Login(false);
	});
	//]]>
</script>

{include file='footer' __disableAds=true}
