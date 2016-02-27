<nav id="topMenu" class="userPanel">
	<ul class="userPanelItems">
		{if $__wcf->user->userID}
			<!-- user menu -->
			<li id="userMenu" class="dropdown">
				<a class="dropdownToggle" data-toggle="userMenu">{if PACKAGE_ID}{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)} {/if}{lang}wcf.user.userNote{/lang}</a>
				<ul class="dropdownMenu">
					{if PACKAGE_ID > 1}
						<li><a href="{link controller='User' object=$__wcf->user forceFrontend=true}{/link}" class="box32">
								<div>{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(32)}</div>
								
								<div class="containerHeadline">
									<h3>{$__wcf->user->username}</h3>
									<small>{lang}wcf.user.myProfile{/lang}</small>
								</div>
							</a></li>
						{if $__wcf->getUserProfileHandler()->canEditOwnProfile()}<li><a href="{link controller='User' object=$__wcf->user forceFrontend=true}editOnInit=true#about{/link}">{lang}wcf.user.editProfile{/lang}</a></li>{/if}
						<li><a href="{link controller='Settings' forceFrontend=true}{/link}">{lang}wcf.user.menu.settings{/lang}</a></li>
						
						{event name='userMenuItems'}
						
						<li class="dropdownDivider"></li>
					{/if}
					<li><a href="{link controller='Logout'}t={@SECURITY_TOKEN}{/link}" onclick="WCF.System.Confirmation.show('{lang}wcf.user.logout.sure{/lang}', $.proxy(function (action) { if (action == 'confirm') window.location.href = $(this).attr('href'); }, this)); return false;">{lang}wcf.user.logout{/lang}</a></li>
				</ul>
			</li>
			
			{if PACKAGE_ID}
				<li id="jumpToPage" class="dropdown">
					<a href="{link forceFrontend=true}{/link}" class="dropdownToggle" data-toggle="jumpToPage"><span class="icon icon32 fa-home"></span> <span>{lang}wcf.global.jumpToPage{/lang}</span></a>
					<ul class="dropdownMenu">
						{foreach from=$__wcf->getPageMenu()->getMenuItems('header') item=_menuItem}
							<li><a href="{$_menuItem->getProcessor()->getLink()}">{lang}{$_menuItem->menuItem}{/lang}</a></li>
						{/foreach}
					</ul>
				</li>
				
				{if $__wcf->session->getPermission('admin.system.package.canUpdatePackage') && $__wcf->getAvailableUpdates()}
					<li>
						<a href="{link controller='PackageUpdate'}{/link}"><span class="icon icon32 fa-refresh"></span> <span>{lang}wcf.acp.package.updates{/lang}</span> <span class="badge badgeInverse">{#$__wcf->getAvailableUpdates()}</span></a>
					</li>
				{/if}
			{/if}
			
			<li id="woltlab" class="dropdown">
				<a class="dropdownToggle" data-toggle="woltlab"><span class="icon icon32 fa-info"></span> <span>WoltLab&reg;</span></a>
				
				<ul class="dropdownMenu">
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