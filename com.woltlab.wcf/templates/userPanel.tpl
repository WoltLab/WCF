{if $__wcf->user->userID}
	<!-- user menu -->
	<li id="userMenu">
		<a class="framed" href="{link controller='User' object=$__wcf->user}{/link}">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(24)} <span>{lang}wcf.user.userNote{/lang}</span></a>
		<div class="interactiveDropdown interactiveDropdownStatic interactiveDropdownUserMenu">
			<div class="interactiveDropdownHeader">
				<span class="interactiveDropdownTitle">{lang}wcf.user.controlPanel{/lang}</span>
				
				{hascontent}
					<ul class="interactiveDropdownLinks">
						{content}
							{event name='userMenuLinks'}
						{/content}
					</ul>
				{/hascontent}
			</div>
			<div class="interactiveDropdownItemsContainer">
				<ul class="interactiveDropdownItems interactiveDropdownItemsUserMenu">
					<li>
						<div class="box64">
							<a href="{link controller='User' object=$__wcf->user}{/link}" class="framed">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(64)}</a>
							
							<div class="containerHeadline">
								<h3><a href="{link controller='User' object=$__wcf->user}{/link}">{$__wcf->user->username}</a></h3>
								{if MODULE_USER_RANK}
									<p>
										{if $__wcf->getUserProfileHandler()->getUserTitle()}
											<span class="badge userTitleBadge{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->cssClassName} {@$__wcf->getUserProfileHandler()->getRank()->cssClassName}{/if}">{$__wcf->getUserProfileHandler()->getUserTitle()}</span>
										{/if}
										{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->rankImage}
											<span class="userRankImage">{@$__wcf->getUserProfileHandler()->getRank()->getImage()}</span>
										{/if}
									</p>
								{/if}
								
								<ul class="interactiveDropdownUserMenuLinkList">
									<li><a href="{link controller='User' object=$__wcf->user}{/link}">{lang}wcf.user.myProfile{/lang}</a></li>
									{if $__wcf->getUserProfileHandler()->canEditOwnProfile()}<li><a href="{link controller='User' object=$__wcf->user}editOnInit=true#about{/link}">{lang}wcf.user.editProfile{/lang}</a></li>{/if}
									{if $__wcf->session->getPermission('admin.general.canUseAcp')}<li><a href="{link isACP=true}{/link}">{lang}wcf.global.acp.short{/lang}</a></li>{/if}
								</ul>
							</div>
						</div>
					</li>
					
					{event name='userMenuItemsBefore'}
					
					{foreach from=$__wcf->getUserMenu()->getMenuItems('') item=menuCategory}
						<li class="dropdownDivider"></li>
						<li class="interactiveDropdownUserMenuItem">
							<div class="box32">
								<div><span class="icon icon32 {@$menuCategory->getIconClassName()}"></span></div>
								
								<div class="containerHeadline">
									<h3>{lang}{$menuCategory->menuItem}{/lang}</h3>
									
									<ul class="interactiveDropdownUserMenuLinkList">
										{foreach from=$__wcf->getUserMenu()->getMenuItems($menuCategory->menuItem) item=menuItem}
											<li><a href="{$menuItem->getProcessor()->getLink()}">{@$menuItem}</a></li>
										{/foreach}
									</ul>
								</div>
							</div>
						</li>
					{/foreach}
					
					{event name='userMenuItemsAfter'}
				</ul>
			</div>
			<a class="interactiveDropdownShowAll" href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" onclick="WCF.Dropdown.Interactive.Handler.close('userMenu'); WCF.System.Confirmation.show('{lang}wcf.user.logout.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;">{lang}wcf.user.logout{/lang}</a>
		</div>
		<script data-relocate="true">
			$(function() {
				new WCF.User.Panel.UserMenu();
			});
		</script>
	</li>
	
	<li><a href="{link controller='Settings'}{/link}" class="noJsOnly" style="display: none"><span class="icon icon16 icon-cogs"></span> <span>{lang}wcf.user.menu.settings{/lang}</span></a></li>
	
	<!-- user notifications -->
	{if !$__hideUserMenu|isset}
		<li id="userNotifications" data-count="{#$__wcf->getUserNotificationHandler()->getNotificationCount()}">
			<a href="{link controller='NotificationList'}{/link}"><span class="icon icon16 icon-bell-alt"></span> <span>{lang}wcf.user.notification.notifications{/lang}</span>{if $__wcf->getUserNotificationHandler()->getNotificationCount()} <span class="badge badgeInverse">{#$__wcf->getUserNotificationHandler()->getNotificationCount()}</span>{/if}</a>
			{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
				<script data-relocate="true">
					//<![CDATA[
					$(function() {
						new WCF.User.Panel.Notification({
							markAllAsReadConfirmMessage: '{lang}wcf.user.notification.markAllAsConfirmed.confirmMessage{/lang}',
							noItems: '{lang}wcf.user.notification.noMoreNotifications{/lang}',
							settingsLink: '{link controller='NotificationSettings' encode=false}{/link}',
							showAllLink: '{link controller='NotificationList' encode=false}{/link}',
							title: '{lang}wcf.user.notification.notifications{/lang}'
						});
					});
					//]]>
				</script>
			{/if}
		</li>
	{/if}
{else}
	{if !$__disableLoginLink|isset}
		<!-- login box -->
		<li id="userLogin">
			<a class="loginLink" href="{link controller='Login'}{/link}">{lang}wcf.user.loginOrRegister{/lang}</a>
			<div id="loginForm" style="display: none;">
				{capture assign='__3rdPartyButtons'}
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
							<a class="button" href="{link controller='LostPassword'}{/link}"><span>{lang}wcf.user.lostPassword{/lang}</span></a>
							<input type="hidden" name="url" value="{$__wcf->session->requestURI}" />
							{@SECURITY_TOKEN_INPUT_TAG}
						</div>
					</fieldset>
					
					{if $__3rdPartyButtons|trim}
						<fieldset>
							<legend>{lang}wcf.user.login.3rdParty{/lang}</legend>
							<ul class="buttonList smallButtons thirdPartyLogin">
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
				{if $__wcf->getModerationQueueManager()->getUnreadModerationCount()}<span class="badge badgeInverse">{#$__wcf->getModerationQueueManager()->getUnreadModerationCount()}</span>{/if}
			</a>
			{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
				<script data-relocate="true">
					//<![CDATA[
					$(function() {
						new WCF.User.Panel.Moderation({
							deletedContent: '{lang}wcf.moderation.showDeletedContent{/lang}',
							deletedContentLink: '{link controller='DeletedContentList' encode=false}{/link}',
							markAllAsReadConfirmMessage: '{lang}wcf.moderation.markAllAsRead.confirmMessage{/lang}',
							noItems: '{lang}wcf.moderation.noMoreItems{/lang}',
							showAllLink: '{link controller='ModerationList' encode=false}{/link}',
							title: '{lang}wcf.moderation.moderation{/lang}'
						});
					});
					//]]>
				</script>
			{/if}
		</li>
	{/if}
	
	{event name='menuItems'}
{/if}

{if $__wcf->user->userID}
	<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" class="noJsOnly" style="display: none"><span class="icon icon16 icon-signout"></span> <span>{lang}wcf.user.logout{/lang}</span></a></li>
{/if}
