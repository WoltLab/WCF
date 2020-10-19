<nav id="topMenu" class="userPanel{if $__wcf->user->userID} userPanelLoggedIn{/if}">
	{if $__wcf->user->userID}
		<span class="userPanelAvatar">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}</span>
	{/if}
	
	<ul class="userPanelItems">
		{if $__wcf->user->userID}
			<!-- user menu -->
			<li id="userMenu">
				<a class="jsTooltip" href="{$__wcf->user->getLink()}" title="{lang}wcf.user.controlPanel{/lang}">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)} <span>{lang}wcf.user.userNote{/lang}</span></a>
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
								<div class="box48">
									{user object=$__wcf->getUserProfileHandler()->getUserProfile() type='avatar48' ariaHidden='true'}
									
									<div class="containerHeadline">
										<h3>
											{user object=$__wcf->getUserProfileHandler()->getUserProfile()}
											{if MODULE_USER_RANK}
												{if $__wcf->getUserProfileHandler()->getUserTitle()}
													<span class="badge userTitleBadge{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->cssClassName} {@$__wcf->getUserProfileHandler()->getRank()->cssClassName}{/if}">{$__wcf->getUserProfileHandler()->getUserTitle()}</span>
												{/if}
												{if $__wcf->getUserProfileHandler()->getRank() && $__wcf->getUserProfileHandler()->getRank()->rankImage}
													<span class="userRankImage">{@$__wcf->getUserProfileHandler()->getRank()->getImage()}</span>
												{/if}
											{/if}
										</h3>
										
										<ul class="inlineList dotSeparated">
											<li><a href="{$__wcf->user->getLink()}">{lang}wcf.user.myProfile{/lang}</a></li>
											{if $__wcf->getUserProfileHandler()->canEditOwnProfile()}<li><a href="{link controller='User' object=$__wcf->user}editOnInit=true#about{/link}" class="jsUserPanelEditProfile">{lang}wcf.user.editProfile{/lang}</a></li>{/if}
											{if $__wcf->session->getPermission('admin.general.canUseAcp')}<li><a href="{link isACP=true}{/link}">{lang}wcf.global.acp.short{/lang}</a></li>{/if}
										</ul>
									</div>
								</div>
							</li>
							
							{event name='userMenuItemsBefore'}
							
							{foreach from=$__wcf->getUserMenu()->getMenuItems('') item=menuCategory}
								<li class="interactiveDropdownUserMenuItem">
									<div class="box48">
										<div><span class="icon icon48 {@$menuCategory->getIconClassName()}"></span></div>
										
										<div class="containerHeadline">
											<h3>{$menuCategory->getTitle()}</h3>
											
											<ul class="inlineList dotSeparated">
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
					<a class="interactiveDropdownShowAll" href="{link controller='Logout'}t={csrfToken type=url}{/link}" onclick="WCF.Dropdown.Interactive.Handler.close('userMenu'); WCF.System.Confirmation.show('{lang}wcf.user.logout.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;">{lang}wcf.user.logout{/lang}</a>
				</div>
				<script data-relocate="true">
					$(function() {
						new WCF.User.Panel.UserMenu();
					});
				</script>
			</li>
			
			<!-- user notifications -->
			{if !$__hideUserMenu|isset}
				<li id="userNotifications" data-count="{#$__wcf->getUserNotificationHandler()->getNotificationCount()}">
					<a class="jsTooltip" href="{link controller='NotificationList'}{/link}" title="{lang}wcf.user.notification.notifications{/lang}"><span class="icon icon32 fa-bell-o"></span> <span>{lang}wcf.user.notification.notifications{/lang}</span>{if $__wcf->getUserNotificationHandler()->getNotificationCount()} <span class="badge badgeUpdate">{#$__wcf->getUserNotificationHandler()->getNotificationCount()}</span>{/if}</a>
					{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
						<script data-relocate="true">
							$(function() {
								new WCF.User.Panel.Notification({
									noItems: '{lang}wcf.user.notification.noMoreNotifications{/lang}',
									settingsLink: '{link controller='NotificationSettings' encode=false}{/link}',
									showAllLink: '{link controller='NotificationList' encode=false}{/link}',
									title: '{lang}wcf.user.notification.notifications{/lang}'
								});
							});
						</script>
					{/if}
				</li>
			{/if}
		{else}
			{if $__wcf->getLanguage()->getLanguages()|count > 1}
				<li id="pageLanguageContainer">
					<script data-relocate="true">
						require(['EventHandler', 'WoltLabSuite/Core/Language/Chooser'], function(EventHandler, LanguageChooser) {
							var languages = {
								{implode from=$__wcf->getLanguage()->getLanguages() item=_language}
									'{@$_language->languageID}': {
										iconPath: '{@$_language->getIconPath()|encodeJS}',
										languageName: '{@$_language|encodeJS}',
										languageCode: '{@$_language->languageCode|encodeJS}'
									}
								{/implode}
							};
								
							var callback = function(listItem) {
								var location;
								var languageCode = elData(listItem, 'language-code');
								var link = elBySel('link[hreflang="' + languageCode + '"]');
								if (link !== null) {
									location = link.href;
								}
								else {
									location = window.location.toString().replace(/#.*/, '').replace(/(\?|&)l=[0-9]+/g, '');
								}
								
								var delimiter = (location.indexOf('?') == -1) ? '?' : '&';
								window.location = location + delimiter + 'l=' + elData(listItem, 'language-id') + window.location.hash;
							};
							
							LanguageChooser.init('pageLanguageContainer', 'pageLanguageID', {@$__wcf->getLanguage()->languageID}, languages, callback);
							EventHandler.add('com.woltlab.wcf.UserMenuMobile', 'more', function(data) {
								if (data.identifier === 'com.woltlab.wcf.language') {
									callback(data.parent);
								}
							});
						});
					</script>
				</li>
			{/if}
			{if !$__disableLoginLink|isset}
				<!-- login box -->
				<li id="userLogin">
					<a class="loginLink" href="{link controller='Login'}{/link}">{lang}wcf.user.loginOrRegister{/lang}</a>
					<div id="loginForm" class="loginForm" style="display: none">
						<form method="post" action="{link controller='Login'}{/link}">
							<section class="section loginFormLogin">
								<h2 class="sectionTitle">{lang}wcf.user.login.login{/lang}</h2>
								
								<dl>
									<dt><label for="username">{lang}wcf.user.usernameOrEmail{/lang}</label></dt>
									<dd>
										<input type="text" id="username" name="username" value="" required class="long" autocomplete="username">
									</dd>
								</dl>
								
								<dl>
									<dt><label for="password">{lang}wcf.user.password{/lang}</label></dt>
									<dd>
										<input type="password" id="password" name="password" value="" class="long" autocomplete="current-password">
										<small><a href="{link controller='LostPassword'}{/link}">{lang}wcf.user.lostPassword{/lang}</a></small>
									</dd>
								</dl>
								
								{event name='fields'}
								
								<div class="userLoginButtons">
									<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
									<input type="hidden" name="url" value="{$__wcf->session->requestURI}">
									{csrfToken}
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
											<ul class="buttonList">
												{content}
													{if FACEBOOK_PUBLIC_KEY !== '' && FACEBOOK_PRIVATE_KEY !== ''}
														<li id="facebookAuth" class="thirdPartyLogin">
															<a href="{link controller='FacebookAuth'}{/link}" class="button thirdPartyLoginButton facebookLoginButton"><span class="icon icon24 fa-facebook-official"></span> <span>{lang}wcf.user.3rdparty.facebook.login{/lang}</span></a>
														</li>
													{/if}
													
													{if GOOGLE_PUBLIC_KEY !== '' && GOOGLE_PRIVATE_KEY !== ''}
														<li id="googleAuth" class="thirdPartyLogin">
															<a href="{link controller='GoogleAuth'}{/link}" class="button thirdPartyLoginButton googleLoginButton"><span class="icon icon24 fa-google"></span> <span>{lang}wcf.user.3rdparty.google.login{/lang}</span></a>
														</li>
													{/if}
												
													{if TWITTER_PUBLIC_KEY !== '' && TWITTER_PRIVATE_KEY !== ''}
														<li id="twitterAuth" class="thirdPartyLogin">
															<a href="{link controller='TwitterAuth'}{/link}" class="button thirdPartyLoginButton twitterLoginButton"><span class="icon icon24 fa-twitter"></span> <span>{lang}wcf.user.3rdparty.twitter.login{/lang}</span></a>
														</li>
													{/if}
													
													{if GITHUB_PUBLIC_KEY !== '' && GITHUB_PRIVATE_KEY !== ''}
														<li id="githubAuth" class="thirdPartyLogin">
															<a href="{link controller='GithubAuth'}{/link}" class="button thirdPartyLoginButton githubLoginButton"><span class="icon icon24 fa-github"></span> <span>{lang}wcf.user.3rdparty.github.login{/lang}</span></a>
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
						$(function() {
							WCF.Language.addObject({
								'wcf.user.button.login': '{jslang}wcf.user.button.login{/jslang}',
								'wcf.user.button.register': '{jslang}wcf.user.button.register{/jslang}',
								'wcf.user.login': '{jslang}wcf.user.login{/jslang}'
							});
							WCF.User.QuickLogin.init();
						});
					</script>
				</li>
			{/if}
		{/if}
		
		{if !$__hideUserMenu|isset}
			{if $__wcf->user->userID && $__wcf->session->getPermission('mod.general.canUseModeration')}
				<li id="outstandingModeration" data-count="{#$__wcf->getModerationQueueManager()->getUnreadModerationCount()}">
					<a class="jsTooltip" href="{link controller='ModerationList'}{/link}" title="{lang}wcf.moderation.moderation{/lang}">
						<span class="icon icon32 fa-exclamation-triangle"></span>
						<span>{lang}wcf.moderation.moderation{/lang}</span>
						{if $__wcf->getModerationQueueManager()->getUnreadModerationCount()}<span class="badge badgeUpdate">{#$__wcf->getModerationQueueManager()->getUnreadModerationCount()}</span>{/if}
					</a>
					{if !OFFLINE || $__wcf->session->getPermission('admin.general.canViewPageDuringOfflineMode')}
						<script data-relocate="true">
							$(function() {
								new WCF.User.Panel.Moderation({
									deletedContent: '{lang}wcf.moderation.showDeletedContent{/lang}',
									deletedContentLink: '{link controller='DeletedContentList' encode=false}{/link}',
									noItems: '{lang}wcf.moderation.noMoreItems{/lang}',
									showAllLink: '{link controller='ModerationList' encode=false}{/link}',
									title: '{lang}wcf.moderation.moderation{/lang}'
								});
							});
						</script>
					{/if}
				</li>
			{/if}
			
			{event name='menuItems'}
		{/if}
		
		<!-- page search -->
		{if !SEARCH_USE_CAPTCHA || $__wcf->user->userID}
			<li class="jsOnly">
				<a href="#" id="userPanelSearchButton" class="jsTooltip" title="{lang}wcf.global.search{/lang}"><span class="icon icon32 fa-search"></span> <span>{lang}wcf.global.search{/lang}</span></a>
			</li>
		{else}
			<li>
				<a href="{link controller='Search'}{/link}" class="jsTooltip" title="{lang}wcf.global.search{/lang}"><span class="icon icon32 fa-search"></span> <span>{lang}wcf.global.search{/lang}</span></a>
				<span id="userPanelSearchButton" style="display: none"></span>
			</li>
		{/if}
	</ul>
</nav>
