{capture assign='pageTitle'}{$user->username} - {lang}wcf.user.members{/lang}{/capture}

{assign var='contentHeader' value=' '}{* necessary to hide default content header in heade.tpl *}

{capture assign='headContent'}
	{event name='javascriptInclude'}
	<script data-relocate="true">
		{if $__wcf->getUser()->userID && $__wcf->getUser()->userID != $user->userID}
			require(['Language', 'WoltLab/WCF/Ui/User/Profile/Menu/Item/Ignore', 'WoltLab/WCF/Ui/User/Profile/Menu/Item/Follow'], function(Language, UiUserProfileMenuItemIgnore, UiUserProfileMenuItemFollow) {
				Language.addObject({
					'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
					'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
					'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
					'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
				});
				
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
			
			{if $isAccessible && $__wcf->user->userID != $user->userID}
				WCF.Language.addObject({
					'wcf.user.ban': '{lang}wcf.user.ban{/lang}',
					'wcf.user.ban.confirmMessage': '{lang}wcf.user.ban.confirmMessage{/lang}',
					'wcf.user.ban.expires': '{lang}wcf.user.ban.expires{/lang}',
					'wcf.user.ban.expires.description': '{lang}wcf.user.ban.expires.description{/lang}',
					'wcf.user.ban.neverExpires': '{lang}wcf.user.ban.neverExpires{/lang}',
					'wcf.user.ban.reason.description': '{lang}wcf.user.ban.reason.description{/lang}',
					'wcf.user.unban': '{lang}wcf.user.unban{/lang}',
					'wcf.user.disableAvatar': '{lang}wcf.user.disableAvatar{/lang}',
					'wcf.user.ban.expires': '{lang}wcf.user.ban.expires{/lang}',
					'wcf.user.ban.expires.description': '{lang}wcf.user.ban.expires.description{/lang}',
					'wcf.user.ban.neverExpires': '{lang}wcf.user.ban.neverExpires{/lang}',
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
					'wcf.user.enableSignature': '{lang}wcf.user.enableSignature{/lang}'
				});
				
				var $userInlineEditor = new WCF.User.InlineEditor('.userHeadline');
				$userInlineEditor.setPermissions({
					canBanUser: {if $__wcf->session->getPermission('admin.user.canBanUser')}true{else}false{/if},
					canDisableAvatar: {if $__wcf->session->getPermission('admin.user.canDisableAvatar')}true{else}false{/if},
					canDisableSignature: {if $__wcf->session->getPermission('admin.user.canDisableSignature')}true{else}false{/if},
					canEditUser: {if $__wcf->session->getPermission('admin.general.canUseAcp') && $__wcf->session->getPermission('admin.user.canEditUser')}true{else}false{/if}
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

{include file='userHeader' assign='boxesTop'}

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
