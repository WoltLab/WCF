{capture assign='pageTitle'}{$user->username} - {lang}wcf.user.members{/lang}{/capture}

{capture assign='headContent'}
	{event name='javascriptInclude'}
	<script data-relocate="true">
		{if !$__wcf->getUser()->isGuest() && $__wcf->getUser()->userID != $user->userID}
			require(['WoltLabSuite/Core/Ui/User/Editor'], function(UiUserEditor) {
				{jsphrase name='wcf.acp.user.disable'}
				{jsphrase name='wcf.acp.user.enable'}
				{jsphrase name='wcf.user.ban'}
				{jsphrase name='wcf.user.banned'}
				{jsphrase name='wcf.user.ban.confirmMessage'}
				{jsphrase name='wcf.user.ban.expires'}
				{jsphrase name='wcf.user.ban.expires.description'}
				{jsphrase name='wcf.user.ban.neverExpires'}
				{jsphrase name='wcf.user.ban.reason.description'}
				{jsphrase name='wcf.user.disableAvatar'}
				{jsphrase name='wcf.user.disableAvatar.confirmMessage'}
				{jsphrase name='wcf.user.disableAvatar.expires'}
				{jsphrase name='wcf.user.disableAvatar.expires.description'}
				{jsphrase name='wcf.user.disableAvatar.neverExpires'}
				{jsphrase name='wcf.user.disableCoverPhoto'}
				{jsphrase name='wcf.user.disableCoverPhoto.confirmMessage'}
				{jsphrase name='wcf.user.disableCoverPhoto.expires'}
				{jsphrase name='wcf.user.disableCoverPhoto.expires.description'}
				{jsphrase name='wcf.user.disableCoverPhoto.neverExpires'}
				{jsphrase name='wcf.user.disableSignature'}
				{jsphrase name='wcf.user.disableSignature.confirmMessage'}
				{jsphrase name='wcf.user.disableSignature.expires'}
				{jsphrase name='wcf.user.disableSignature.expires.description'}
				{jsphrase name='wcf.user.disableSignature.neverExpires'}
				{jsphrase name='wcf.user.edit'}
				{jsphrase name='wcf.user.enableAvatar'}
				{jsphrase name='wcf.user.enableCoverPhoto'}
				{jsphrase name='wcf.user.enableSignature'}
				{jsphrase name='wcf.user.unban'}
				
				{if $isAccessible}
					UiUserEditor.init();
				{/if}
			});
		{/if}

		$(function() {
			{if !$__wcf->getUser()->isGuest() && $__wcf->getUser()->userID != $user->userID}
				{jsphrase name='wcf.user.activityPoint'}
			{/if}

			{if $user->canEdit() || ($__wcf->getUser()->userID == $user->userID && $user->canEditOwnProfile())}
				{jsphrase name='wcf.user.editProfile'}
				
				new WCF.User.Profile.Editor({$user->userID}, {if $editOnInit}true{else}false{/if});
			{/if}
			
			{event name='javascriptInit'}
		});

		require(['WoltLabSuite/Core/Controller/User/Profile'], ({ setup }) => {
			setup({$user->userID});
		});
	</script>
	
	<noscript>
		<style>
			#profileContent > .tabMenu > ul > li:not(:first-child) {
				display: none !important;
			}
			
			#profileContent > .tabMenuContent:not(:first-of-type) {
				display: none !important;
			}
		</style>
	</noscript>
{/capture}

{capture assign='beforeMaincontent'}
	<div class="layoutBoundary">
		{unsafe:$userProfileHeaderView}
	</div>
{/capture}

{include file='userSidebar' assign='sidebarRight'}

{include file='header'}

{if !$user->isProtected()}
	<div id="profileContent" class="section tabMenuContainer userProfileContent" data-active="{$__wcf->getUserProfileMenu()->getActiveMenuItem($userID)->getIdentifier()}">
		<nav class="tabMenu">
			<ul>
				{foreach from=$__wcf->getUserProfileMenu()->getMenuItems() item=menuItem}
					{if $menuItem->getContentManager()->isVisible($userID)}
						<li><a href="#{$menuItem->getIdentifier()|rawurlencode}">{$menuItem}</a></li>
					{/if}
				{/foreach}
			</ul>
		</nav>
		
		{foreach from=$__wcf->getUserProfileMenu()->getMenuItems() item=menuItem}
			{if $menuItem->getContentManager()->isVisible($userID)}
				<div id="{$menuItem->getIdentifier()}" class="tabMenuContent" data-menu-item="{$menuItem->menuItem}">
					{if $menuItem === $__wcf->getUserProfileMenu()->getActiveMenuItem($userID)}
						{unsafe:$profileContent}
					{/if}
				</div>
			{/if}
		{/foreach}
	</div>
{else}
	<woltlab-core-notice type="info">{lang}wcf.user.profile.protected{/lang}</woltlab-core-notice>
{/if}

{include file='footer'}
