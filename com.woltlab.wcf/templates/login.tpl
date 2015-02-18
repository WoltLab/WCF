{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.login{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			new WCF.User.Login(false);
		})
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">
{include file='header' __disableLoginLink=true __disableAds=true}

<header class="boxHeadline">
	<h1>{lang}wcf.user.login{/lang}</h1>
</header>

{include file='userNotice'}

{include file='formError'}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtons'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{@$loginController}" id="loginForm">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.user.login.data{/lang}</legend>
			
			<dl{if $errorField == 'username'} class="formError"{/if}>
				<dt><label for="username">{lang}wcf.user.usernameOrEmail{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" required="required" class="medium" />
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
			
			{if !REGISTER_DISABLED}
				<dl>
					<dt>{lang}wcf.user.login.action{/lang}</dt>
					<dd>
						<label><input type="radio" name="action" value="register" /> {lang}wcf.user.login.action.register{/lang}</label>
						<label><input type="radio" name="action" value="login" checked="checked" /> {lang}wcf.user.login.action.login{/lang}</label>
					</dd>
				</dl>
			{/if}
			
			<dl{if $errorField == 'password'} class="formError"{/if}>
				<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
				<dd>
					<input type="password" id="password" name="password" value="{$password}" class="medium" />
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
			
			{if $supportsPersistentLogins}
				<dl>
					<dt></dt>
					<dd>
						<label for="useCookies"><input type="checkbox" id="useCookies" name="useCookies" value="1" {if $useCookies}checked="checked" {/if}/> {lang}wcf.user.useCookies{/lang}</label>
					</dd>
				</dl>
			{/if}
			
			{event name='fields'}
			
			<dl>
				<dt></dt>
				<dd>
					<ul class="buttonList smallButtons">
						<li><a class="button small" href="{link controller='LostPassword'}{/link}"><span>{lang}wcf.user.lostPassword{/lang}</span></a></li>
						{if !REGISTER_DISABLED && REGISTER_ACTIVATION_METHOD == 1}<li><a class="button small" href="{link controller='RegisterActivation'}{/link}"><span>{lang}wcf.user.registerActivation{/lang}</span></a></li>{/if}
						{event name='buttons'}
					</ul>
				</dd>
			</dl>
		</fieldset>
		
		{hascontent}
			<fieldset>
				<legend>{lang}wcf.user.login.3rdParty{/lang}</legend>
				
				<dl>
					<dt></dt>
					<dd>
						<ul class="buttonList smallButtons">
							{content}
								{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
									<li id="githubAuth" class="3rdPartyAuth">
										<a href="{link controller='GithubAuth'}{/link}" class="thirdPartyLoginButton githubLoginButton"><span class="icon icon16 icon-github"></span> <span>{lang}wcf.user.3rdparty.github.login{/lang}</span></a>
									</li>
								{/if}
								
								{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}
									<li id="twitterAuth" class="3rdPartyAuth">
										<a href="{link controller='TwitterAuth'}{/link}" class="thirdPartyLoginButton twitterLoginButton"><span class="icon icon16 icon-twitter"></span> <span>{lang}wcf.user.3rdparty.twitter.login{/lang}</span></a>
									</li>
								{/if}
								
								{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}
									<li id="facebookAuth" class="3rdPartyAuth">
										<a href="{link controller='FacebookAuth'}{/link}" class="thirdPartyLoginButton facebookLoginButton"><span class="icon icon16 icon-facebook"></span> <span>{lang}wcf.user.3rdparty.facebook.login{/lang}</span></a>
									</li>
								{/if}
								
								{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}
									<li id="googleAuth" class="3rdPartyAuth">
										<a href="{link controller='GoogleAuth'}{/link}" class="thirdPartyLoginButton googleLoginButton"><span class="icon icon16 icon-google-plus"></span> <span>{lang}wcf.user.3rdparty.google.login{/lang}</span></a>
									</li>
								{/if}
								
								{event name='3rdpartyButtons'}
							{/content}
						</ul>
					</dd>
				</dl>
			</fieldset>
		{/hascontent}
		
		{event name='fieldsets'}
		
		{include file='captcha'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="url" value="{$url}" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer' __disableAds=true}

</body>
</html>
