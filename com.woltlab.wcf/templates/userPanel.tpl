{if $__wcf->user->userID}
	<!-- user menu -->
	<li id="userMenu" class="dropdown">
		<a class="dropdownToggle framed" data-toggle="userMenu" href="{link controller='User' object=$__wcf->user}{/link}">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(24)} <span>{lang}wcf.user.userNote{/lang}</span></a>
		<ul class="dropdownMenu">
			<li><a href="{link controller='User' object=$__wcf->user}{/link}" class="box32">
				<div class="framed">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}</div>
				
				<div class="containerHeadline">
					<h3>{$__wcf->user->username}</h3>
					<small>{lang}wcf.user.myProfile{/lang}</small>
				</div>
			</a></li>
			{if $__wcf->getUserProfileHandler()->canEditOwnProfile()}<li><a href="{link controller='User' object=$__wcf->user}editOnInit=true#about{/link}">{lang}wcf.user.editProfile{/lang}</a></li>{/if}
			<li><a href="{link controller='Settings'}{/link}">{lang}wcf.user.menu.settings{/lang}</a></li>
			
			{event name='userMenuItems'}
			
			{if $__wcf->session->getPermission('admin.general.canUseAcp')}
				<li class="dropdownDivider"></li>
				<li><a href="{link isACP=true}{/link}">{lang}wcf.global.acp.short{/lang}</a></li>
			{/if}
			<li class="dropdownDivider"></li>
			<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" onclick="WCF.System.Confirmation.show('{lang}wcf.user.logout.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;">{lang}wcf.user.logout{/lang}</a></li>
		</ul>
	</li>
	
	<li><a href="{link controller='Settings'}{/link}" class="noJsOnly" style="display: none"><span class="icon icon16 icon-cogs"></span> <span>{lang}wcf.user.menu.settings{/lang}</span></a></li>
	
	<!-- user notifications -->
	{if !$__hideUserMenu|isset}
		<li id="userNotifications" data-count="{#$__wcf->getUserNotificationHandler()->getNotificationCount()}">
			<a href="{link controller='NotificationList'}{/link}"><span class="icon icon16 icon-bell-alt"></span> <span>{lang}wcf.user.notification.notifications{/lang}</span>{if $__wcf->getUserNotificationHandler()->getNotificationCount()} <span class="badge badgeInverse">{#$__wcf->getUserNotificationHandler()->getNotificationCount()}</span>{/if}</a>
			<script data-relocate="true">
				//<![CDATA[
				$(function() {
					WCF.Language.addObject({
						'wcf.user.notification.count': '{lang}wcf.user.notification.count{/lang}',
						'wcf.user.notification.markAllAsConfirmed': '{lang}wcf.user.notification.markAllAsConfirmed{/lang}',
						'wcf.user.notification.markAllAsConfirmed.confirmMessage': '{lang}wcf.user.notification.markAllAsConfirmed.confirmMessage{/lang}',
						'wcf.user.notification.noMoreNotifications': '{lang}wcf.user.notification.noMoreNotifications{/lang}',
						'wcf.user.notification.showAll': '{lang}wcf.user.notification.showAll{/lang}'
					});
					
					new WCF.Notification.UserPanel('{link controller='NotificationList'}{/link}');
				});
				//]]>
			</script>
		</li>
	{/if}
{else}
	{if !$__disableLoginLink|isset}
		<!-- login box -->
		<li id="userLogin">
			<a class="loginLink" href="{link controller='Login'}{/link}"><span>{lang}wcf.user.loginOrRegister{/lang}</span></a>
			<div id="loginForm" style="display: none;">
				{capture assign='__3rdPartyButtons'}
					{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
						<li id="githubAuth" class="3rdPartyAuth">
							<a href="{link controller='GithubAuth'}{/link}" class="button"><span class="icon icon16 icon-github"></span> <span>{lang}wcf.user.3rdparty.github.login{/lang}</span></a>
						</li>{*
					*}{/if}{*
					
					*}{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}{*
						*}<li id="twitterAuth" class="3rdPartyAuth">
							<a href="{link controller='TwitterAuth'}{/link}" class="button"><span class="icon icon16 icon-twitter"></span> <span>{lang}wcf.user.3rdparty.twitter.login{/lang}</span></a>
						</li>{*
					*}{/if}{*
					
					*}{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}{*
						*}<li id="facebookAuth" class="3rdPartyAuth">
							<a href="{link controller='FacebookAuth'}{/link}" class="button"><span class="icon icon16 icon-facebook"></span> <span>{lang}wcf.user.3rdparty.facebook.login{/lang}</span></a>
						</li>{*
					*}{/if}{*
					
					*}{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}{*
						*}<li id="googleAuth" class="3rdPartyAuth">
							<a href="{link controller='GoogleAuth'}{/link}" class="button"><span class="icon icon16 icon-google-plus"></span> <span>{lang}wcf.user.3rdparty.google.login{/lang}</span></a>
						</li>
					{/if}
				{/capture}
				
				<form method="post" action="{link controller='Login'}{/link}">
					<fieldset>
						{if $__3rdPartyButtons|trim}<legend>{lang}wcf.user.login{/lang}</legend>{/if}
						
						<dl>
							<dt><label for="username">{lang}wcf.user.usernameOrEmail{/lang}</label></dt>
							<dd>
								<input type="text" id="username" name="username" value="" required="required" class="long" />
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
						
						<dl>
							<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
							<dd>
								<input type="password" id="password" name="password" value="" class="long" />
							</dd>
						</dl>
						
						{if $__wcf->getUserAuthenticationFactory()->getUserAuthentication()->supportsPersistentLogins()}
							<dl>
								<dt></dt>
								<dd><label><input type="checkbox" id="useCookies" name="useCookies" value="1" checked="checked" /> {lang}wcf.user.useCookies{/lang}</label></dd>
							</dl>
						{/if}
						
						{event name='loginFields'}
						
						<div class="formSubmit">
							<input type="submit" id="loginSubmitButton" name="submitButton" value="{lang}wcf.user.button.login{/lang}" accesskey="s" />
							<input type="hidden" name="url" value="{$__wcf->session->requestURI}" />
						</div>
					</fieldset>
					
					{if $__3rdPartyButtons|trim}
						<fieldset>
							<legend>{lang}wcf.user.login.3rdParty{/lang}</legend>
							<ul class="buttonGroup thirdPartyLogin">
								{@$__3rdPartyButtons}	
							</ul>
						</fieldset>
					{/if}
				</form>
			</div>
			
			<script data-relocate="true">
				//<![CDATA[
				$(function() {
					WCF.Language.addObject({
						'wcf.user.button.login': '{lang}wcf.user.button.login{/lang}',
						'wcf.user.button.register': '{lang}wcf.user.button.register{/lang}',
						'wcf.user.login': '{lang}wcf.user.login{/lang}'
					});
					new WCF.User.Login(true);
				});
				//]]>
			</script>
		</li>
	{/if}
	{if $__wcf->getLanguage()->getLanguages()|count > 1}
		<li id="pageLanguageContainer">
			<script data-relocate="true">
				//<![CDATA[
				$(function() {
					var $languages = {
						{implode from=$__wcf->getLanguage()->getLanguages() item=language}
							'{@$language->languageID}': {
								iconPath: '{@$language->getIconPath()}',
								languageName: '{$language}'
							}
						{/implode}
					};
					
					new WCF.Language.Chooser('pageLanguageContainer', 'languageID', {@$__wcf->getLanguage()->languageID}, $languages, function(item) {
						var $location = window.location.toString().replace(/#.*/, '').replace(/(\?|&)l=[0-9]+/g, '');
						var $delimiter = ($location.indexOf('?') == -1) ? '?' : '&';
						
						window.location = $location + $delimiter + 'l=' + item.data('languageID') + window.location.hash;
					});
				});
				//]]>
			</script>
		</li>
	{/if}
{/if}

{if !$__hideUserMenu|isset}
	{if $__wcf->user->userID && $__wcf->session->getPermission('mod.general.canUseModeration')}
		<li id="outstandingModeration" data-count="{#$__wcf->getModerationQueueManager()->getOutstandingModerationCount()}">
			<a href="{link controller='ModerationList'}{/link}">
				<span class="icon icon16 icon-warning-sign"></span>
				<span>{lang}wcf.moderation.moderation{/lang}</span>
				{if $__wcf->getModerationQueueManager()->getOutstandingModerationCount()}<span class="badge badgeInverse">{#$__wcf->getModerationQueueManager()->getOutstandingModerationCount()}</span>{/if}
			</a>
			<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Moderation{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
			<script data-relocate="true">
				//<![CDATA[
				$(function() {
					WCF.Language.addObject({
						'wcf.moderation.noMoreItems': '{lang}wcf.moderation.noMoreItems{/lang}',
						'wcf.moderation.showAll': '{lang}wcf.moderation.showAll{/lang}',
						'wcf.moderation.showDeletedContent': '{lang}wcf.moderation.showDeletedContent{/lang}'
					});
					
					new WCF.Moderation.UserPanel('{link controller='ModerationList'}{/link}', '{link controller='DeletedContentList'}{/link}');
				});
				//]]>
			</script>
		</li>
	{/if}
	
	{event name='menuItems'}
{/if}

{if $__wcf->user->userID}
	<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" class="noJsOnly" style="display: none"><span class="icon icon16 icon-signout"></span> <span>{lang}wcf.user.logout{/lang}</span></a></li>
{/if}
