{capture assign='pageTitle'}{$user->username} - {lang}wcf.user.members{/lang}{/capture}

{capture assign='headContent'}
	{event name='javascriptInclude'}
	<script data-relocate="true">
		{if $__wcf->getUser()->userID && $__wcf->getUser()->userID != $user->userID}
			require(['Language', 'WoltLab/WCF/Ui/User/Editor', 'WoltLab/WCF/Ui/User/Profile/Menu/Item/Ignore', 'WoltLab/WCF/Ui/User/Profile/Menu/Item/Follow'], function(Language, UiUserEditor, UiUserProfileMenuItemIgnore, UiUserProfileMenuItemFollow) {
				Language.addObject({
					'wcf.acp.user.disable': '{lang}wcf.acp.user.disable{/lang}',
					'wcf.acp.user.enable': '{lang}wcf.acp.user.enable{/lang}',
					'wcf.user.ban': '{lang}wcf.user.ban{/lang}',
					'wcf.user.banned': '{lang}wcf.user.banned{/lang}',
					'wcf.user.ban.confirmMessage': '{lang}wcf.user.ban.confirmMessage{/lang}',
					'wcf.user.ban.expires': '{lang}wcf.user.ban.expires{/lang}',
					'wcf.user.ban.expires.description': '{lang}wcf.user.ban.expires.description{/lang}',
					'wcf.user.ban.neverExpires': '{lang}wcf.user.ban.neverExpires{/lang}',
					'wcf.user.ban.reason.description': '{lang}wcf.user.ban.reason.description{/lang}',
					'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
					'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
					'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
					'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}',
					'wcf.user.disableAvatar': '{lang}wcf.user.disableAvatar{/lang}',
					'wcf.user.disableAvatar.confirmMessage': '{lang}wcf.user.disableAvatar.confirmMessage{/lang}',
					'wcf.user.disableAvatar.expires': '{lang}wcf.user.disableAvatar.expires{/lang}',
					'wcf.user.disableAvatar.expires.description': '{lang}wcf.user.disableAvatar.expires.description{/lang}',
					'wcf.user.disableAvatar.neverExpires': '{lang}wcf.user.disableAvatar.neverExpires{/lang}',
					'wcf.user.disableSignature': '{lang}wcf.user.disableSignature{/lang}',
					'wcf.user.disableSignature.confirmMessage': '{lang}wcf.user.disableSignature.confirmMessage{/lang}',
					'wcf.user.disableSignature.expires': '{lang}wcf.user.disableSignature.expires{/lang}',
					'wcf.user.disableSignature.expires.description': '{lang}wcf.user.disableSignature.expires.description{/lang}',
					'wcf.user.disableSignature.neverExpires': '{lang}wcf.user.disableSignature.neverExpires{/lang}',
					'wcf.user.edit': '{lang}wcf.user.edit{/lang}',
					'wcf.user.enableAvatar': '{lang}wcf.user.enableAvatar{/lang}',
					'wcf.user.enableSignature': '{lang}wcf.user.enableSignature{/lang}',
					'wcf.user.unban': '{lang}wcf.user.unban{/lang}'
				});
				
				{if $isAccessible}
					UiUserEditor.init();
				{/if}
				
				{if !$user->isIgnoredUser($__wcf->user->userID)}
					new UiUserProfileMenuItemFollow({@$user->userID}, {if $__wcf->getUserProfileHandler()->isFollowing($user->userID)}true{else}false{/if});
				{/if}
				
				{if !$user->getPermission('user.profile.cannotBeIgnored')}
					new UiUserProfileMenuItemIgnore({@$user->userID}, {if $__wcf->getUserProfileHandler()->isIgnoredUser($user->userID)}true{else}false{/if});
				{/if}
			});
		{/if}
		
		//<![CDATA[
		$(function() {
			{if $__wcf->getUser()->userID && $__wcf->getUser()->userID != $user->userID}
				WCF.Language.addObject({
					'wcf.user.activityPoint': '{lang}wcf.user.activityPoint{/lang}'
				});
			{/if}
			
			new WCF.User.Profile.TabMenu({@$user->userID});
			
			{if $user->canEdit() || ($__wcf->getUser()->userID == $user->userID && $user->canEditOwnProfile())}
				WCF.Language.addObject({
					'wcf.user.editProfile': '{lang}wcf.user.editProfile{/lang}'
				});
				
				new WCF.User.Profile.Editor({@$user->userID}, {if $editOnInit}true{else}false{/if});
			{/if}
			
			{if $followingCount > 7}
				var $followingList = null;
				$('#followingAll').click(function() {
					if ($followingList === null) {
						$followingList = new WCF.User.List('wcf\\data\\user\\follow\\UserFollowingAction', $('#followingAll').parents('fieldset').children('legend').text().replace(/ \d+$/, ''), { userID: {@$user->userID} });
					}
					
					$followingList.open();
				});
			{/if}
			{if $followerCount > 7}
				var $followerList = null;
				$('#followerAll').click(function() {
					if ($followerList === null) {
						$followerList = new WCF.User.List('wcf\\data\\user\\follow\\UserFollowAction', $('#followerAll').parents('fieldset').children('legend').text().replace(/ \d+$/, ''), { userID: {@$user->userID} });
					}
					
					$followerList.open();
				});
			{/if}
			{if $visitorCount > 7}
				var $visitorList = null;
				$('#visitorAll').click(function() {
					if ($visitorList === null) {
						$visitorList = new WCF.User.List('wcf\\data\\user\\profile\\visitor\\UserProfileVisitorAction', $('#visitorAll').parents('fieldset').children('legend').text().replace(/ \d+$/, ''), { userID: {@$user->userID} });
					}
					
					$visitorList.open();
				});
			{/if}
			
			{if $__wcf->session->getPermission('user.profile.canReportContent')}
				WCF.Language.addObject({
					'wcf.moderation.report.reportContent': '{lang}wcf.user.profile.report{/lang}',
					'wcf.moderation.report.success': '{lang}wcf.moderation.report.success{/lang}'
				});
				new WCF.Moderation.Report.Content('com.woltlab.wcf.user', '.jsReportUser');
			{/if}
			
			{event name='javascriptInit'}
		});
		//]]>
	</script>
	
	<noscript>
		<style type="text/css">
			#profileContent > .tabMenu > ul > li:not(:first-child) {
				display: none !important;
			}
			
			#profileContent > .tabMenuContent:not(:first-of-type) {
				display: none !important;
			}
		</style>
	</noscript>
{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader userProfileUser"{if $isAccessible}
		data-object-id="{@$user->userID}"
		{if $__wcf->session->getPermission('admin.user.canBanUser')}
			data-banned="{@$user->banned}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canDisableAvatar')}
			data-disable-avatar="{@$user->disableAvatar}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canDisableSignature')}
			data-disable-signature="{@$user->disableSignature}"
		{/if}
		{if $__wcf->session->getPermission('admin.user.canEnableUser')}
			data-is-disabled="{if $user->activationCode}true{else}false{/if}"
		{/if}
		{/if}>
		<div class="contentHeaderIcon">
			{if $user->userID == $__wcf->user->userID}
				<a href="{link controller='AvatarEdit'}{/link}" class="jsTooltip" title="{lang}wcf.user.avatar.edit{/lang}">{@$user->getAvatar()->getImageTag(128)}</a>
			{else}
				<span>{@$user->getAvatar()->getImageTag(128)}</span>
			{/if}
		</div>
		
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">
				{$user->username}
				{if $user->banned}<span class="icon icon16 fa-lock jsTooltip jsUserBanned" title="{lang}wcf.user.banned{/lang}"></span>{/if}
			</h1>
			{if MODULE_USER_RANK}
				{if $user->getUserTitle()}
					<span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>
				{/if}
				{if $user->getRank() && $user->getRank()->rankImage}
					<span class="userRankImage">{@$user->getRank()->getImage()}</span>
				{/if}
			{/if}
			<div class="contentDescription">
				<ul class="inlineList commaSeparated">
					{if $user->isVisibleOption('gender') && $user->gender}<li>{lang}wcf.user.gender.{if $user->gender == 1}male{else}female{/if}{/lang}</li>{/if}
					{if $user->isVisibleOption('birthday') && $user->getAge()}<li>{@$user->getAge()}</li>{/if}
					{if $user->isVisibleOption('location') && $user->location}<li>{lang}wcf.user.membersList.location{/lang}</li>{/if}
					{if $user->getOldUsername()}<li>{lang}wcf.user.profile.oldUsername{/lang}</li>{/if}
					<li>{lang}wcf.user.membersList.registrationDate{/lang}</li>
					{event name='userDataRow1'}
				</ul>
				
				{if $user->canViewOnlineStatus() && $user->getLastActivityTime()}
					<ul class="inlineList commaSeparated">
						<li>{lang}wcf.user.usersOnline.lastActivity{/lang}: {@$user->getLastActivityTime()|time}</li>
						{if $user->getCurrentLocation()}<li>{@$user->getCurrentLocation()}</li>{/if}
					</ul>
				{/if}
				
				<dl class="plain inlineDataList">
					{include file='userInformationStatistics'}
					
					{if $user->profileHits}
						<dt{if $user->getProfileAge() > 1} title="{lang}wcf.user.profileHits.hitsPerDay{/lang}"{/if}>{lang}wcf.user.profileHits{/lang}</dt>
						<dd>{#$user->profileHits}</dd>
					{/if}
				</dl>
			</div>
		</div>
		
		<nav class="contentHeaderNavigation">
			<ul class="userProfileButtonContainer">
				{hascontent}
					<li class="dropdown">
						<a class="jsTooltip button dropdownToggle" title="{lang}wcf.user.profile.customization{/lang}"><span class="icon icon32 fa-pencil"></span> <span class="invisible">{lang}wcf.user.profile.customization{/lang}</span></a>
						<ul class="dropdownMenu userProfileButtonMenu" data-menu="customization">
							{content}
								{event name='menuCustomization'}
								
								{if $user->userID == $__wcf->user->userID}
									<li><a href="{link controller='AvatarEdit'}{/link}">{lang}wcf.user.avatar.edit{/lang}</a></li>
								{/if}
								
								{if $user->canEdit() || ($__wcf->getUser()->userID == $user->userID && $user->canEditOwnProfile())}
									<li><a href="#" class="jsButtonEditProfile">{lang}wcf.user.editProfile{/lang}</a></li>
								{/if}
							{/content}
						</ul>
					</li>
				{/hascontent}
				
				{hascontent}
					<li class="dropdown">
						<a class="jsTooltip button dropdownToggle" title="{lang}wcf.user.profile.user{/lang}"><span class="icon icon32 fa-user"></span> <span class="invisible">{lang}wcf.user.profile.dropdown.interaction{/lang}</span></a>
						<ul class="dropdownMenu userProfileButtonMenu" data-menu="interaction">
							{content}
								{event name='menuInteraction'}
								
								{if $user->userID != $__wcf->user->userID}
									{if $user->isAccessible('canViewEmailAddress') || $__wcf->session->getPermission('admin.user.canEditMailAddress')}
										<li><a href="mailto:{@$user->getEncodedEmail()}">{lang}wcf.user.button.mail{/lang}</a></li>
									{elseif $user->isAccessible('canMail') && $__wcf->session->getPermission('user.profile.canMail')}
										<li><a href="{link controller='Mail' object=$user}{/link}">{lang}wcf.user.button.mail{/lang}</a></li>
									{/if}
								{/if}
								
								{if $user->userID != $__wcf->user->userID && $__wcf->session->getPermission('user.profile.canReportContent')}
									<li class="jsReportUser" data-object-id="{@$user->userID}"><a href="#">{lang}wcf.user.profile.report{/lang}</a></li>
								{/if}
							{/content}
						</ul>
					</li>
				{/hascontent}
				
				{hascontent}
					<li class="dropdown">
						<a class="jsTooltip button dropdownToggle" title="{lang}wcf.user.searchUserContent{/lang}"><span class="icon icon32 fa-search"></span> <span class="invisible">{lang}wcf.user.searchUserContent{/lang}</span></a>
						<ul class="dropdownMenu userProfileButtonMenu" data-menu="search">
							{content}{event name='menuSearch'}{event name='quickSearchItems'}{/content}
						</ul>
					</li>
				{/hascontent}
				
				{hascontent}
					<li class="dropdown">
						<a class="jsTooltip button dropdownToggle" title="{lang}wcf.user.profile.management{/lang}"><span class="icon icon32 fa-cog"></span> <span class="invisible">{lang}wcf.user.profile.dropdown.management{/lang}</span></a>
						<ul class="dropdownMenu userProfileButtonMenu" data-menu="management">
							{content}
								{event name='menuManagement'}
								
								{if $isAccessible && $__wcf->user->userID != $user->userID && ($__wcf->session->getPermission('admin.user.canBanUser') || $__wcf->session->getPermission('admin.user.canDisableAvatar') || $__wcf->session->getPermission('admin.user.canDisableSignature') || ($__wcf->session->getPermission('admin.general.canUseAcp') && $__wcf->session->getPermission('admin.user.canEditUser')){event name='moderationDropdownPermissions'})}
									{if $__wcf->session->getPermission('admin.user.canBanUser')}<li><a href="#" class="jsButtonUserBan">{lang}wcf.user.{if $user->banned}un{/if}ban{/lang}</a></li>{/if}
									{if $__wcf->session->getPermission('admin.user.canDisableAvatar')}<li><a href="#" class="jsButtonUserDisableAvatar">{lang}wcf.user.{if $user->disableAvatar}enable{else}disable{/if}Avatar{/lang}</a></li>{/if}
									{if $__wcf->session->getPermission('admin.user.canDisableSignature')}<li><a href="#" class="jsButtonUserDisableSignature">{lang}wcf.user.{if $user->disableSignature}enable{else}disable{/if}Signature{/lang}</a></li>{/if}
									{if $__wcf->session->getPermission('admin.user.canEnableUser')}<li><a href="#" class="jsButtonUserEnable">{lang}wcf.acp.user.{if $user->activationCode}enable{else}disable{/if}{/lang}</a></li>{/if}
									
									<li><a href="{link controller='UserEdit' object=$user isACP=true}{/link}" class="jsUserInlineEditor">{lang}wcf.user.edit{/lang}</a></li>
								{/if}
							{/content}
						</ul>
					</li>
				{/hascontent}
				
				{event name='contentHeaderNavigation'}
			</ul>
		</nav>
		
	</header>
{/capture}

{include file='userSidebar' assign='sidebarRight'}

{include file='header'}

{if !$user->isProtected()}
	<div id="profileContent" class="section tabMenuContainer userProfileContent" data-active="{$__wcf->getUserProfileMenu()->getActiveMenuItem($userID)->getIdentifier()}">
		<nav class="tabMenu">
			<ul>
				{foreach from=$__wcf->getUserProfileMenu()->getMenuItems() item=menuItem}
					{if $menuItem->getContentManager()->isVisible($userID)}
						<li><a href="{$__wcf->getAnchor($menuItem->getIdentifier())}">{lang}wcf.user.profile.menu.{@$menuItem->menuItem}{/lang}</a></li>
					{/if}
				{/foreach}
			</ul>
		</nav>
		
		{foreach from=$__wcf->getUserProfileMenu()->getMenuItems() item=menuItem}
			{if $menuItem->getContentManager()->isVisible($userID)}
				<div id="{$menuItem->getIdentifier()}" class="tabMenuContent" data-menu-item="{$menuItem->menuItem}">
					{if $menuItem === $__wcf->getUserProfileMenu()->getActiveMenuItem($userID)}
						{@$profileContent}
					{/if}
				</div>
			{/if}
		{/foreach}
	</div>
{else}
	<p class="info">{lang}wcf.user.profile.protected{/lang}</p>
{/if}

{include file='footer'}
