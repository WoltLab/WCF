{include file='documentHeader'}

<head>
	<title>{lang}wcf.user.profile{/lang} - {lang}wcf.user.members{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{link controller='User' object=$user}{/link}" />
	
	<script data-relocate="true" src="{@$__wcf->getPath('wcf')}js/WCF.User{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
	{event name='javascriptInclude'}
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			{if $__wcf->getUser()->userID && $__wcf->getUser()->userID != $user->userID}
				WCF.Language.addObject({
					'wcf.user.activityPoint': '{lang}wcf.user.activityPoint{/lang}',
					'wcf.user.button.follow': '{lang}wcf.user.button.follow{/lang}',
					'wcf.user.button.unfollow': '{lang}wcf.user.button.unfollow{/lang}',
					'wcf.user.button.ignore': '{lang}wcf.user.button.ignore{/lang}',
					'wcf.user.button.unignore': '{lang}wcf.user.button.unignore{/lang}'
				});
				
				{if !$user->getPermission('user.profile.cannotBeIgnored')}
					new WCF.User.Profile.IgnoreUser({@$user->userID}, {if $__wcf->getUserProfileHandler()->isIgnoredUser($user->userID)}true{else}false{/if});
				{/if}
				
				new WCF.User.Profile.Follow({@$user->userID}, {if $__wcf->getUserProfileHandler()->isFollowing($user->userID)}true{else}false{/if});
			{/if}
			
			new WCF.User.Profile.TabMenu({@$user->userID});
			
			WCF.TabMenu.init();
			
			{if $user->canEdit() || ($__wcf->getUser()->userID == $user->userID && $user->canEditOwnProfile())}
				WCF.Language.addObject({
					'wcf.user.editProfile': '{lang}wcf.user.editProfile{/lang}',
				});
				
				new WCF.User.Profile.Editor({@$user->userID}, {if $editOnInit}true{else}false{/if});
			{/if}
			
			{if $user->activityPoints}
				WCF.Language.addObject({
					'wcf.user.activityPoint': '{lang}wcf.user.activityPoint{/lang}'
				});
				
				WCF.User.Profile.ActivityPointList.init();
			{/if}
			
			{if $followingCount > 10}
				var $followingList = null;
				$('#followingAll').click(function() {
					if ($followingList === null) {
						$followingList = new WCF.User.List('wcf\\data\\user\\follow\\UserFollowingAction', $('#followingAll').parents('fieldset').children('legend').text().replace(/ \d+$/, ''), { userID: {@$user->userID} });
					}
					
					$followingList.open();
				});
			{/if}
			{if $followerCount > 10}
				var $followerList = null;
				$('#followerAll').click(function() {
					if ($followerList === null) {
						$followerList = new WCF.User.List('wcf\\data\\user\\follow\\UserFollowAction', $('#followerAll').parents('fieldset').children('legend').text().replace(/ \d+$/, ''), { userID: {@$user->userID} });
					}
					
					$followerList.open();
				});
			{/if}
			{if $visitorCount > 10}
				var $visitorList = null;
				$('#visitorAll').click(function() {
					if ($visitorList === null) {
						$visitorList = new WCF.User.List('wcf\\data\\user\\profile\\visitor\\UserProfileVisitorAction', $('#visitorAll').parents('fieldset').children('legend').text().replace(/ \d+$/, ''), { userID: {@$user->userID} });
					}
					
					$visitorList.open();
				});
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
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='userSidebar' assign='sidebar'}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline userHeadline">
	<span class="framed invisible">{@$user->getAvatar()->getImageTag(48)}</span>
	
	<h1>{$user->username}{if MODULE_USER_RANK && $user->getUserTitle()} <span class="badge userTitleBadge{if $user->getRank() && $user->getRank()->cssClassName} {@$user->getRank()->cssClassName}{/if}">{$user->getUserTitle()}</span>{/if}</h1>
	
	<ul class="dataList">
		{if $user->gender}<li>{lang}wcf.user.gender.{if $user->gender == 1}male{else}female{/if}{/lang}</li>{/if}
		{if $user->getAge()}<li>{@$user->getAge()}</li>{/if}
		{if $user->location}<li>{lang}wcf.user.membersList.location{/lang}</li>{/if}
		{if $user->getOldUsername()}<li>{lang}wcf.user.profile.oldUsername{/lang}</li>{/if}
		<li>{lang}wcf.user.membersList.registrationDate{/lang}</li>
		{event name='userDataRow1'}
	</ul>
	{if $user->canViewOnlineStatus() && $user->getLastActivityTime()}
		<dl class="plain inlineDataList">
			<dt>{lang}wcf.user.usersOnline.lastActivity{/lang}</dt>
			<dd>{@$user->getLastActivityTime()|time}{if $user->getCurrentLocation()}, {@$user->getCurrentLocation()}{/if}</dd>
			{event name='userDataRow2'}
		</dl>
	{/if}
	<nav class="jsMobileNavigation buttonGroupNavigation">
		<ul id="profileButtonContainer" class="buttonGroup">
			{hascontent}
				<li class="dropdown">
					<a class="button dropdownToggle jsTooltip" title="{lang}wcf.user.searchUserContent{/lang}"><span class="icon icon16 icon-search"></span> <span class="invisible">{lang}wcf.user.searchUserContent{/lang}</span></a>
					<ul class="dropdownMenu">
						{content}
							{event name='quickSearchItems'}
						{/content}
					</ul>
				</li>
			{/hascontent}
			{if $user->userID != $__wcf->user->userID}{if $user->isAccessible('canViewEmailAddress')}<li><a class="button jsTooltip" href="mailto:{@$user->getEncodedEmail()}" title="{lang}wcf.user.button.mail{/lang}"><span class="icon icon16 icon-envelope-alt"></span> <span class="invisible">{lang}wcf.user.button.mail{/lang}</span></a></li>{elseif $user->isAccessible('canMail') && $__wcf->session->getPermission('user.profile.canMail')}<li><a class="button jsTooltip" href="{link controller='Mail' object=$user}{/link}" title="{lang}wcf.user.button.mail{/lang}"><span class="icon icon16 icon-envelope-alt"></span> <span class="invisible">{lang}wcf.user.button.mail{/lang}</span></a></li>{/if}{/if}
			{event name='buttons'}
		</ul>
	</nav>
</header>

{include file='userNotice'}

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

<section id="profileContent" class="marginTop tabMenuContainer" data-active="{$__wcf->getUserProfileMenu()->getActiveMenuItem()->getIdentifier()}">
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
			<div id="{$menuItem->getIdentifier()}" class="container tabMenuContent" data-menu-item="{$menuItem->menuItem}">
				{if $menuItem === $__wcf->getUserProfileMenu()->getActiveMenuItem()}
					{@$profileContent}
				{/if}
			</div>
		{/if}
	{/foreach}
</section>

{include file='footer'}

</body>
</html>
