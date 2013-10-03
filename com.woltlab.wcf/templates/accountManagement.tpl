{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.accountManagement{/lang} - {lang}wcf.user.usercp{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='userMenuSidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.user.accountManagement{/lang}</h1>
</header>

{include file='userNotice'}

{include file='formError'}

<p class="warning">{lang}wcf.user.accountManagement.warning{/lang}</p>

{if $success|isset && $success|count > 0}
	<div class="success">
		{foreach from=$success item=successMessage}
			<p>{lang}{@$successMessage}{/lang}</p>
		{/foreach}
	</div>
{/if}

{assign var=__authProvider value=$__wcf->getUserProfileHandler()->getAuthProvider()}

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

<form method="post" action="{link controller='AccountManagement'}{/link}">
	<div class="container containerPadding marginTop">
		{if !$__authProvider}
			<fieldset>
				<legend><label for="password">{lang}wcf.user.password{/lang}</label></legend>
				
				<dl{if $errorField == 'password'} class="formError"{/if}>
					<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
					<dd>
						<input type="password" id="password" name="password" value="" required="required" class="medium" />
						{if $errorField == 'password'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								{if $errorType == 'false'}{lang}wcf.user.password.error.false{/lang}{/if}
							</small>
						{/if}
						<small>{lang}wcf.user.accountManagement.password.description{/lang}</small>
					</dd>
				</dl>
				
				<dl>
					<dt></dt>
					<dd>
						<ul class="buttonList smallButtons">
							<li><a class="button small" href="{link controller='LostPassword'}{/link}"><span>{lang}wcf.user.lostPassword{/lang}</span></a></li>
						</ul>
					</dd>
				</dl>
				
				{event name='passwordFields'}
			</fieldset>
		{/if}
		
		{if $__wcf->getSession()->getPermission('user.profile.canRename')}
			<fieldset>
				<legend><label for="username">{lang}wcf.user.changeUsername{/lang}</label></legend>
					
				<dl{if $errorField == 'username'} class="formError"{/if}>
					<dt><label for="username">{lang}wcf.user.newUsername{/lang}</label></dt>
					<dd>
						<input type="text" id="username" name="username" value="{$username}" required="required" pattern="^[^,]{ldelim}{REGISTER_USERNAME_MIN_LENGTH},{REGISTER_USERNAME_MAX_LENGTH}}$" class="medium" />
							
						{if $errorField == 'username'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								{if $errorType == 'notValid'}{lang}wcf.user.username.error.notValid{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.user.username.error.notUnique{/lang}{/if}
								{if $errorType == 'alreadyRenamed'}{lang}wcf.user.username.error.alreadyRenamed{/lang}{/if}
							</small>
						{/if}
						{if $renamePeriod > 0}
							<small>{lang}wcf.user.changeUsername.description{/lang}</small>
						{/if}
					</dd>
				</dl>
				
				{event name='changeUsernameFields'}
			</fieldset>
		{/if}
		
		{if !$__authProvider}
			<fieldset>
				<legend><label for="newPassword">{lang}wcf.user.changePassword{/lang}</label></legend>
				
				<dl{if $errorField == 'newPassword'} class="formError"{/if}>
					<dt><label for="newPassword">{lang}wcf.user.newPassword{/lang}</label></dt>
					<dd>
						<input type="password" id="newPassword" name="newPassword" value="{$newPassword}" class="medium" />
							
						{if $errorField == 'newPassword'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								{if $errorType == 'notSecure'}{lang}wcf.user.password.error.notSecure{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'confirmNewPassword'} class="formError"{/if}>
					<dt><label for="confirmNewPassword">{lang}wcf.user.confirmPassword{/lang}</label></dt>
					<dd>
						<input type="password" id="confirmNewPassword" name="confirmNewPassword" value="{$confirmNewPassword}" class="medium" />
							
						{if $errorField == 'confirmNewPassword'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								{if $errorType == 'notEqual'}{lang}wcf.user.confirmPassword.error.notEqual{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				{event name='changePasswordFields'}
			</fieldset>
		{/if}
		
		{if $__wcf->getSession()->getPermission('user.profile.canChangeEmail')}
			<fieldset>
				<legend><label for="email">{lang}wcf.user.changeEmail{/lang}</label></legend>
				
				<dl{if $errorField == 'email'} class="formError"{/if}>
					<dt><label for="email">{lang}wcf.user.newEmail{/lang}</label></dt>
					<dd>
						<input type="email" id="email" name="email" value="{$email}" class="medium" />
							
						{if $errorField == 'email'}
							<small class="innerError">
								{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
								{if $errorType == 'notValid'}{lang}wcf.user.email.error.notValid{/lang}{/if}
								{if $errorType == 'notUnique'}{lang}wcf.user.email.error.notUnique{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl{if $errorField == 'confirmEmail'} class="formError"{/if}>
					<dt><label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label></dt>
					<dd>
						<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" class="medium" />
							
						{if $errorField == 'confirmEmail'}
							<small class="innerError">
								{if $errorType == 'notEqual'}{lang}wcf.user.confirmEmail.error.notEqual{/lang}{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				{event name='changeEmailFields'}
				
				{if REGISTER_ACTIVATION_METHOD == 1 && $__wcf->getUser()->reactivationCode != 0}
					<dl>
						<dt></dt>
						<dd>
							<ul class="buttonList smallButtons">
								<li><a class="button small" href="{link controller='EmailActivation'}{/link}"><span>{lang}wcf.user.emailActivation{/lang}</span></a></li>
							</ul>
						</dd>
					</dl>
				{/if}
			</fieldset>
		{/if}
		
		{if $__wcf->getSession()->getPermission('user.profile.canQuit')}
			<fieldset>
				<legend>{lang}wcf.user.quit{/lang}</legend>
				
				{if $quitStarted}
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" name="cancelQuit" value="1" {if $cancelQuit == 1}checked="checked" {/if}/> {lang}wcf.user.quit.cancel{/lang}</label>
						</dd>
					</dl>
				{else}
					<dl>
						<dt></dt>
						<dd>
							<label><input type="checkbox" name="quit" value="1" {if $quit == 1}checked="checked" {/if}/> {lang}wcf.user.quit.sure{/lang}</label>
							<small>{lang}wcf.user.quit.description{/lang}</small>
						</dd>
					</dl>
				{/if}
				
				{event name='quitFields'}
			</fieldset>
		{/if}
		
		{hascontent}
			<fieldset id="3rdParty">
				<legend>{lang}wcf.user.3rdparty{/lang}</legend>
				
				{content}
					{if $__authProvider}
						<dl>
							<dt>{lang}wcf.user.3rdparty.{@$__authProvider}{/lang}</dt>
							<dd>
								<label><input type="checkbox" name="{@$__authProvider}Disconnect" value="1" /> {lang}wcf.user.3rdparty.{@$__authProvider}.disconnect{/lang}</label>
							</dd>
						</dl>
					{elseif !$__wcf->getUser()->hasAdministrativeAccess()}
						{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
							<dl>
								<dt>{lang}wcf.user.3rdparty.github{/lang}</dt>
								<dd>
									{if $__wcf->getSession()->getVar('__githubToken')}
										<label><input type="checkbox" name="githubConnect" value="1"{if $githubConnect} checked="checked"{/if} /> {lang}wcf.user.3rdparty.github.connect{/lang}</label>
									{else}
										<a href="{link controller='GithubAuth'}{/link}" class="button small"><span class="icon icon16 icon-github"></span> <span>{lang}wcf.user.3rdparty.github.connect{/lang}</span></a>
									{/if}
								</dd>
							</dl>
						{/if}
						
						{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}
							<dl>
								<dt>{lang}wcf.user.3rdparty.twitter{/lang}</dt>
								<dd>
									{if $__wcf->getSession()->getVar('__twitterData')}
										<label><input type="checkbox" name="twitterConnect" value="1"{if $twitterConnect} checked="checked"{/if} /> {lang}wcf.user.3rdparty.twitter.connect{/lang}</label>
									{else}
										<a href="{link controller='TwitterAuth'}{/link}" class="button small"><span class="icon icon16 icon-twitter"></span> <span>{lang}wcf.user.3rdparty.twitter.connect{/lang}</span></a>
									{/if}
								</dd>
							</dl>
						{/if}
						
						{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}
							<dl>
								<dt>{lang}wcf.user.3rdparty.facebook{/lang}</dt>
								<dd>
									{if $__wcf->getSession()->getVar('__facebookData')}
										<label><input type="checkbox" name="facebookConnect" value="1"{if $facebookConnect} checked="checked"{/if} /> {lang}wcf.user.3rdparty.facebook.connect{/lang}</label>
									{else}
										<a href="{link controller='FacebookAuth'}{/link}" class="button small"><span class="icon icon16 icon-facebook"></span> <span>{lang}wcf.user.3rdparty.facebook.connect{/lang}</span></a>
									{/if}
								</dd>
							</dl>
						{/if}
						
						{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}
							<dl>
								<dt>{lang}wcf.user.3rdparty.google{/lang}</dt>
								<dd>
									{if $__wcf->getSession()->getVar('__googleData')}
										<label><input type="checkbox" name="googleConnect" value="1"{if $googleConnect} checked="checked"{/if} /> {lang}wcf.user.3rdparty.google.connect{/lang}</label>
									{else}
										<a href="{link controller='GoogleAuth'}{/link}" class="button small"><span class="icon icon16 icon-google-plus"></span> <span>{lang}wcf.user.3rdparty.google.connect{/lang}</span></a>
									{/if}
								</dd>
							</dl>
						{/if}
					{/if}
					
					{event name='3rdpartyFields'}
				{/content}
			</fieldset>
		{/hascontent}
		
		{event name='fieldsets'}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}

</body>
</html>
