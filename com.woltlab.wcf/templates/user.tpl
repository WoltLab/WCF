{capture assign='pageTitle'}{$user->username} - {lang}wcf.user.members{/lang}{/capture}

{capture assign='headContent'}
	{event name='javascriptInclude'}
	<script data-relocate="true">
		$(function() {
			{if $__wcf->getUser()->userID && $__wcf->getUser()->userID != $user->userID}
				WCF.Language.addObject({
					'wcf.user.activityPoint': '{jslang}wcf.user.activityPoint{/jslang}'
				});
			{/if}

			{if $user->canEdit() || ($__wcf->getUser()->userID == $user->userID && $user->canEditOwnProfile())}
				WCF.Language.addObject({
					'wcf.user.editProfile': '{jslang}wcf.user.editProfile{/jslang}'
				});
				
				new WCF.User.Profile.Editor({$user->userID}, {if $editOnInit}true{else}false{/if});
			{/if}
			
			{event name='javascriptInit'}
		});

		require(['WoltLabSuite/Core/Controller/User/Profile'], ({ setup }) => {
			setup({$user->userID});
		});
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
