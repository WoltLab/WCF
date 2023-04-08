<nav id="topMenu" class="userPanel">
	<ul class="userPanelItems">
		{if $__wcf->user->userID}
			{if PACKAGE_ID}
				<li id="userMenu" class="dropdown">
					<a href="#" class="dropdownToggle jsTooltip" title="{$__wcf->user->username}">{@$__wcf->getUserProfileHandler()->getAvatar()->getImageTag(24)}</a>
					<ul class="dropdownMenu dropdownMenuUserPanel" data-dropdown-alignment-horizontal="right">
						<li><a href="{link controller='Logout'}t={csrfToken type=url}{/link}">{lang}wcf.user.logout{/lang}</a></li>
					</ul>
				</li>
				
				<li id="jumpToPage">
					<a href="{link forceFrontend=true}{/link}" class="jsTooltip" title="{lang}wcf.global.jumpToPage{/lang}">
						{icon size=16 name='house'}
						{icon size=32 name='house'}
					</a>
				</li>
				
				{if $__wcf->session->getPermission('admin.configuration.package.canUpdatePackage') && $__wcf->getAvailableUpdates()}
					<li>
						<a href="{link controller='PackageUpdate'}{/link}" class="jsTooltip" title="{lang}wcf.acp.package.updates{/lang}">
							{icon size=16 name='arrows-rotate'}
							{icon size=32 name='arrows-rotate'}
							<span class="badge badgeUpdate">{#$__wcf->getAvailableUpdates()}</span>
						</a>
					</li>
				{/if}
			{/if}
			
			<li id="woltlab" class="dropdown">
				<a href="#" class="dropdownToggle jsTooltip" title="WoltLab&reg;">
					{icon size=16 name='info'}
					{icon size=32 name='info'}
				</a>
				
				<ul class="dropdownMenu dropdownMenuUserPanel" data-dropdown-alignment-horizontal="right">
					<li><a class="externalURL" href="https://www.woltlab.com/{if $__wcf->getLanguage()->getFixedLanguageCode() === 'de'}de/{/if}"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{lang}wcf.acp.index.woltlab.website{/lang}</a></li>
					<li><a class="externalURL" href="https://manual.woltlab.com/{if $__wcf->getLanguage()->getFixedLanguageCode() === 'de'}de{else}en{/if}/"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{lang}wcf.acp.index.woltlab.manual{/lang}</a></li>
					<li><a class="externalURL" href="https://www.woltlab.com/community/"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{lang}wcf.acp.index.woltlab.forums{/lang}</a></li>
					<li><a class="externalURL" href="https://www.woltlab.com/ticket-add/"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{lang}wcf.acp.index.woltlab.tickets{/lang}</a></li>
					<li><a class="externalURL" href="https://www.woltlab.com/pluginstore/"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank" rel="noopener"{/if}>{lang}wcf.acp.index.woltlab.pluginStore{/lang}</a></li>
				</ul>
			</li>

			<li>
				<a
					href="#"
					role="button"
					class="page__colorScheme jsButtonStyleColorScheme jsTooltip"
					title="{lang}wcf.style.setColorScheme{/lang}"
				>
					<span class="iconWrapper page__colorScheme--dark">
						{icon size=16 name='moon' type='solid'}
					</span>
					<span class="iconWrapper page__colorScheme--light">
						{icon size=16 name='sun' type='solid'}
					</span>
				</a>
			</li>
		{/if}
		
		{event name='menuItems'}
	</ul>
</nav>
