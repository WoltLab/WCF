<nav id="topMenu" class="userPanel">
	<ul class="userPanelItems">
		{if $__wcf->user->userID}
			{if PACKAGE_ID}
				<li id="userMenu" class="dropdown">
					<a href="#" class="dropdownToggle jsTooltip" title="{$__wcf->user->username}">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}</a>
					<ul class="dropdownMenu" data-dropdown-alignment-horizontal="right">
						<li><a href="{link controller='User' object=$__wcf->user forceFrontend=true}{/link}">{lang}wcf.user.myProfile{/lang}</small></a></li>
						{if $__wcf->getUserProfileHandler()->canEditOwnProfile()}<li><a href="{link controller='User' object=$__wcf->user forceFrontend=true}editOnInit=true#about{/link}">{lang}wcf.user.editProfile{/lang}</a></li>{/if}
						<li><a href="{link controller='Settings' forceFrontend=true}{/link}">{lang}wcf.user.menu.settings{/lang}</a></li>
						
						{event name='userMenuItems'}
						
						<li class="dropdownDivider"></li>
						<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" onclick="WCF.System.Confirmation.show('{lang}wcf.user.logout.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;">{lang}wcf.user.logout{/lang}</a></li>
					</ul>
				</li>
				
				<li id="jumpToPage" class="dropdown">
					<a href="{link forceFrontend=true}{/link}" class="dropdownToggle jsTooltip" title="{lang}wcf.global.jumpToPage{/lang}"><span class="icon icon32 fa-home"></span></a>
					<ul class="dropdownMenu" data-dropdown-alignment-horizontal="right">
						{foreach from=$__wcf->getFrontendMenu()->getMenuItemNodeList() item=_menuItem}
							{if !$_menuItem->getMenuItem()->parentItemID && $_menuItem->getMenuItem()->getPage()}
								<li><a href="{$_menuItem->getMenuItem()->getPage()->getLink()}">{$_menuItem->getMenuItem()->getPage()}</a></li>
							{/if}
						{/foreach}
					</ul>
				</li>
				
				{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage') && $__wcf->getAvailableUpdates()}
					<li>
						<a href="{link controller='PackageUpdate'}{/link}" class="jsTooltip" title="{lang}wcf.acp.package.updates{/lang}"><span class="icon icon32 fa-refresh"></span> <span class="badge badgeUpdate">{#$__wcf->getAvailableUpdates()}</span></a>
					</li>
				{/if}
			{/if}
			
			<li id="woltlab" class="dropdown">
				<a href="#" class="dropdownToggle jsTooltip" title="WoltLab&reg;"><span class="icon icon32 fa-info"></span></a>
				
				<ul class="dropdownMenu" data-dropdown-alignment-horizontal="right">
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://www.woltlab.com"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.website{/lang}</a></li>
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://community.woltlab.com"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.forums{/lang}</a></li>
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://www.woltlab.com/ticket-add/"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.tickets{/lang}</a></li>
					<li><a class="externalURL" href="{@$__wcf->getPath()}acp/dereferrer.php?url={"https://pluginstore.woltlab.com"|rawurlencode}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>{lang}wcf.acp.index.woltlab.pluginStore{/lang}</a></li>
				</ul>
			</li>
		{/if}
		
		{event name='menuItems'}
	</ul>
</nav>
