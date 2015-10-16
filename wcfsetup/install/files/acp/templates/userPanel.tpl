<nav id="topMenu" class="userPanel">
	<ul class="userPanelItems">
		{if $__wcf->user->userID}
			<!-- user menu -->
			<li id="userMenu">
				<a class="framed" href="{link controller='User' object=$__wcf->user}{/link}">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)} <span>{lang}wcf.user.userNote{/lang}</span></a>
			</li>
			
			<li><a href="{link controller='Settings'}{/link}" class="noJsOnly" style="display: none"><span class="icon icon16 fa-cogs"></span> <span>{lang}wcf.user.menu.settings{/lang}</span></a></li>
			
			{if PACKAGE_ID > 1}
				<li id="jumpToPage" class="dropdown">
					<a href="{link forceFrontend=true}{/link}" class="dropdownToggle" data-toggle="jumpToPage"><span class="icon icon16 icon-home"></span> <span>{lang}wcf.global.jumpToPage{/lang}</span></a>
					<ul class="dropdownMenu">
						{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=_menuItem}
							<li><a href="{$_menuItem->getProcessor()->getLink()}">{lang}{$_menuItem->menuItem}{/lang}</a></li>
						{/foreach}
					</ul>
				</li>
				
				{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage') && $__wcf->getAvailableUpdates()}
					<li>
						<a href="{link controller='PackageUpdate'}{/link}"><span class="icon icon16 icon-refresh"></span> <span>{lang}wcf.acp.package.updates{/lang}</span> <span class="badge badgeInverse">{#$__wcf->getAvailableUpdates()}</span></a>
					</li>
				{/if}
			{/if}
			
			<li id="woltlab" class="dropdown">
				<a class="dropdownToggle" data-toggle="woltlab"><span class="icon icon16 icon-info-sign"></span> <span>WoltLab&reg;</span></a>
				
				<ul class="dropdownMenu">
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://www.woltlab.com"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.website{/lang}</a></li>
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://community.woltlab.com"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.forums{/lang}</a></li>
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://www.woltlab.com/ticket-add/"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.tickets{/lang}</a></li>
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://pluginstore.woltlab.com"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.pluginStore{/lang}</a></li>
				</ul>
			</li>
		{/if}
		
		{event name='menuItems'}
		
		{if $__wcf->user->userID}
			<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" class="noJsOnly" style="display: none"><span class="icon icon16 fa-sign-out"></span> <span>{lang}wcf.user.logout{/lang}</span></a></li>
		{/if}
	</ul>
</nav>