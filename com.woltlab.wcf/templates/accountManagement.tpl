{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{include file='formError'}

<p class="warning" role="status">{lang}wcf.user.accountManagement.warning{/lang}</p>

{if $success|isset && $success|count > 0}
	<div class="success" role="status">
		{foreach from=$success item=successMessage}
			<p>{lang}{@$successMessage}{/lang}</p>
		{/foreach}
	</div>
{/if}

{assign var=__authProvider value=$__wcf->getUserProfileHandler()->getAuthProvider()}

<form method="post" action="{link controller='AccountManagement'}{/link}">
	{if !$__authProvider}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.password{/lang}</h2>
			
			<dl{if $errorField == 'password'} class="formError"{/if}>
				<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
				<dd>
					<input type="password" id="password" name="password" value="" required class="medium" autocomplete="current-password">
					{if $errorField == 'password'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'false'}{lang}wcf.user.password.error.false{/lang}{/if}
						</small>
					{/if}
					<small>{lang}wcf.user.accountManagement.password.description{/lang}</small>
					<small><a href="{link controller='LostPassword'}{/link}">{lang}wcf.user.lostPassword{/lang}</a></small>
				</dd>
			</dl>
			
			{event name='passwordFields'}
		</section>
	{/if}
	
	{if $__wcf->getSession()->getPermission('user.profile.canRename')}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.changeUsername{/lang}</h2>
				
			<dl{if $errorField == 'username'} class="formError"{/if}>
				<dt><label for="username">{lang}wcf.user.newUsername{/lang}</label></dt>
				<dd>
					<input type="text" id="username" name="username" value="{$username}" required pattern="^[^,]{ldelim}{REGISTER_USERNAME_MIN_LENGTH},{REGISTER_USERNAME_MAX_LENGTH}}$" class="medium" autocomplete="username">
						
					{if $errorField == 'username'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'invalid'}{lang}wcf.user.username.error.invalid{/lang}{/if}
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
		</section>
	{/if}
	
	{if !$__authProvider}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.changePassword{/lang}</h2>
			
			<dl{if $errorField == 'newPassword'} class="formError"{/if}>
				<dt><label for="newPassword">{lang}wcf.user.newPassword{/lang}</label></dt>
				<dd>
					<input type="password" id="newPassword" name="newPassword" value="{$newPassword}" class="medium" autocomplete="new-password" passwordrules="{$passwordRulesAttributeValue}">
						
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
					<input type="password" id="confirmNewPassword" name="confirmNewPassword" value="{$confirmNewPassword}" class="medium" autocomplete="new-password" passwordrules="{$passwordRulesAttributeValue}">
						
					{if $errorField == 'confirmNewPassword'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'notEqual'}{lang}wcf.user.confirmPassword.error.notEqual{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='changePasswordFields'}
			
			<script data-relocate="true">
				require(['WoltLabSuite/Core/Ui/User/PasswordStrength', 'Language'], function (PasswordStrength, Language) {
					{include file='passwordStrengthLanguage'}
					
					var relatedInputs = [];
					if (elById('username')) relatedInputs.push(elById('username'));
					if (elById('email')) relatedInputs.push(elById('email'));
					
					new PasswordStrength(elById('newPassword'), {
						relatedInputs: relatedInputs,
						staticDictionary: [
							'{$__wcf->user->username|encodeJS}',
							'{$__wcf->user->email|encodeJS}',
						]
					});
				})
			</script>
		</section>
	{/if}
	
	{if $__wcf->getSession()->getPermission('user.profile.canChangeEmail')}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.changeEmail{/lang}</h2>
			
			<dl{if $errorField == 'email'} class="formError"{/if}>
				<dt><label for="email">{lang}wcf.user.newEmail{/lang}</label></dt>
				<dd>
					<input type="email" id="email" name="email" value="{$email}" class="medium">
						
					{if $errorField == 'email'}
						<small class="innerError">
							{if $errorType == 'empty'}{lang}wcf.global.form.error.empty{/lang}{/if}
							{if $errorType == 'invalid'}{lang}wcf.user.email.error.invalid{/lang}{/if}
							{if $errorType == 'notUnique'}{lang}wcf.user.email.error.notUnique{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'confirmEmail'} class="formError"{/if}>
				<dt><label for="confirmEmail">{lang}wcf.user.confirmEmail{/lang}</label></dt>
				<dd>
					<input type="email" id="confirmEmail" name="confirmEmail" value="{$confirmEmail}" class="medium">
						
					{if $errorField == 'confirmEmail'}
						<small class="innerError">
							{if $errorType == 'notEqual'}{lang}wcf.user.confirmEmail.error.notEqual{/lang}{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='changeEmailFields'}
			
			{if $__wcf->user->mustSelfEmailConfirm() && $__wcf->getUser()->reactivationCode != 0}
				<dl>
					<dt></dt>
					<dd>
						<small>{lang newEmail=$__wcf->user->newEmail}wcf.user.changeEmail.needReactivation{/lang}</small>
						<small><a href="{link controller='EmailActivation'}{/link}"><span>{lang}wcf.user.emailActivation{/lang}</span></a></small>
					</dd>
				</dl>
			{/if}
		</section>
	{/if}
	
	{if $__wcf->getSession()->getPermission('user.profile.canQuit')}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.user.quit{/lang}</h2>
			
			{if $quitStarted}
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="cancelQuit" value="1"{if $cancelQuit == 1} checked{/if}> {lang}wcf.user.quit.cancel{/lang}</label>
					</dd>
				</dl>
			{else}
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" name="quit" value="1"{if $quit == 1} checked{/if}> {lang}wcf.user.quit.sure{/lang}</label>
						<small>{lang}wcf.user.quit.description{/lang}</small>
					</dd>
				</dl>
			{/if}
			
			{event name='quitFields'}
		</section>
	{/if}
	
	{hascontent}
		<section class="section" id="3rdParty">
			<h2 class="sectionTitle">{lang}wcf.user.3rdparty{/lang}</h2>
			
			{content}
				{if $__authProvider}
					<dl>
						<dt>{lang}wcf.user.3rdparty.{@$__authProvider}{/lang}</dt>
						<dd>
							<label><input type="checkbox" name="{@$__authProvider}Disconnect" value="1"> {lang}wcf.user.3rdparty.{@$__authProvider}.disconnect{/lang}</label>
						</dd>
					</dl>
				{elseif !$__wcf->getUser()->hasAdministrativeAccess()}
					{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
						<dl>
							<dt>{lang}wcf.user.3rdparty.github{/lang}</dt>
							<dd>
								{if $__wcf->getSession()->getVar('__githubToken')}
									<label><input type="checkbox" name="githubConnect" value="1"{if $githubConnect} checked{/if}> {lang}wcf.user.3rdparty.github.connect{/lang}</label>
								{else}
									<a href="{link controller='GithubAuth'}{/link}" class="thirdPartyLoginButton githubLoginButton button"><span class="icon icon24 fa-github"></span> <span>{lang}wcf.user.3rdparty.github.connect{/lang}</span></a>
								{/if}
							</dd>
						</dl>
					{/if}
					
					{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}
						<dl>
							<dt>{lang}wcf.user.3rdparty.twitter{/lang}</dt>
							<dd>
								{if $__wcf->getSession()->getVar('__twitterData')}
									<label><input type="checkbox" name="twitterConnect" value="1"{if $twitterConnect} checked{/if}> {lang}wcf.user.3rdparty.twitter.connect{/lang}</label>
								{else}
									<a href="{link controller='TwitterAuth'}{/link}" class="thirdPartyLoginButton twitterLoginButton button"><span class="icon icon24 fa-twitter"></span> <span>{lang}wcf.user.3rdparty.twitter.connect{/lang}</span></a>
								{/if}
							</dd>
						</dl>
					{/if}
					
					{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}
						<dl>
							<dt>{lang}wcf.user.3rdparty.facebook{/lang}</dt>
							<dd>
								{if $__wcf->getSession()->getVar('__facebookData')}
									<label><input type="checkbox" name="facebookConnect" value="1"{if $facebookConnect} checked{/if}> {lang}wcf.user.3rdparty.facebook.connect{/lang}</label>
								{else}
									<a href="{link controller='FacebookAuth'}{/link}" class="thirdPartyLoginButton facebookLoginButton button"><span class="icon icon24 fa-facebook"></span> <span>{lang}wcf.user.3rdparty.facebook.connect{/lang}</span></a>
								{/if}
							</dd>
						</dl>
					{/if}
					
					{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}
						<dl>
							<dt>{lang}wcf.user.3rdparty.google{/lang}</dt>
							<dd>
								{if $__wcf->getSession()->getVar('__googleData')}
									<label><input type="checkbox" name="googleConnect" value="1"{if $googleConnect} checked{/if}> {lang}wcf.user.3rdparty.google.connect{/lang}</label>
								{else}
									<a href="{link controller='GoogleAuth'}{/link}" class="thirdPartyLoginButton googleLoginButton button"><span class="icon icon24 fa-google"></span> <span>{lang}wcf.user.3rdparty.google.connect{/lang}</span></a>
								{/if}
							</dd>
						</dl>
					{/if}
					
					{event name='3rdpartyFields'}
				{/if}
			{/content}
		</section>
	{/hascontent}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer' __disableAds=true}
