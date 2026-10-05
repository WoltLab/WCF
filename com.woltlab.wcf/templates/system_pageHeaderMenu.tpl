{* The variables of `__menu` are required by its template listeners, see `TemplateEngine::SYSTEM_TEMPLATE_LISTENER_ALIASES`. *}
{assign var='menuIdentifier' value='com.woltlab.wcf.MainMenu'}
{assign var='__mainMenu' value=$__wcf->getBoxHandler()->getBoxByIdentifier($menuIdentifier)->getMenu()}
{assign var='menuItemNodeList' value=$__mainMenu->getMenuItemNodeList()}
{assign var='menuTitle' value=$__mainMenu->getTitle()}
<nav id="mainMenu" class="mainMenu" aria-label="{$menuTitle}">
	<div class="mainMenuMobileOnly mainMenuHead">
		<span class="mainMenuTitle">{lang}wcf.menu.page{/lang}</span>
		<button type="button" class="mainMenuClose" data-drawer-close aria-label="{lang}wcf.global.button.close{/lang}">
			{icon size=24 name='xmark'}
		</button>
	</div>

	{if $__wcf->user->isGuest()}
		<div class="mainMenuMobileOnly mainMenuGuest">
			{if $__userAuthConfig->canRegister}
				<a href="{link controller='Register'}{/link}" class="button buttonPrimary" rel="nofollow">{lang}wcf.user.button.register{/lang}</a>
			{/if}
			<a href="{link controller='Login' url=$__wcf->getRequestURI()}{/link}" class="button" rel="nofollow">{lang}wcf.user.button.login{/lang}</a>
		</div>
	{/if}

	<ol class="boxMenu">
		{event name='menuBefore'}

		{foreach from=$menuItemNodeList item=menuItemNode}
			<li class="{if $menuItemNode->isActiveNode()}active{/if}{if $menuItemNode->hasChildren()} boxMenuHasChildren{/if}" data-identifier="{$menuItemNode->identifier}">
				<a
					{anchorAttributes url=$menuItemNode->getURL() appendClassname=false}
					class="boxMenuLink"
					{if $menuItemNode->getOutstandingItems() > 0}
						aria-label="{$menuItemNode->getTitle()} {lang}wcf.page.menu.outstandingItems{/lang}"
					{/if}
					{if $menuItemNode->isCurrentNode()} aria-current="page"{/if}
				>
					<span class="boxMenuLinkTitle">{$menuItemNode->getTitle()}</span>
					{if $menuItemNode->getOutstandingItems() > 0}
						<span class="boxMenuLinkOutstandingItems badge badgeUpdate" aria-hidden="true">{#$menuItemNode->getOutstandingItems()}</span>
					{/if}
				</a>

				{if $menuItemNode->hasChildren()}
					<button
						type="button"
						class="boxMenuToggle"
						aria-expanded="false"
						aria-controls="mainMenuItem{$menuItemNode->itemID}"
						aria-label="{lang title=$menuItemNode->getTitle()}wcf.menu.page.button.toggle{/lang}"
					>
						{icon name='chevron-down' type='solid'}
					</button>
					<ol id="mainMenuItem{$menuItemNode->itemID}" class="boxMenuDepth{$menuItemNode->getDepth()}">
				{else}
					</li>
				{/if}

				{if !$menuItemNode->hasChildren() && $menuItemNode->isLastSibling()}
					{unsafe:"</ol></li>"|str_repeat:$menuItemNode->getOpenParentNodes()}
				{/if}
		{/foreach}

		{event name='menuAfter'}

		{* Receives the items that do not fit into the bar, the overflow detection runs on the client. *}
		<li class="mainMenuOverflow boxMenuHasChildren" hidden>
			<button
				type="button"
				class="boxMenuLink boxMenuToggle mainMenuOverflowToggle"
				aria-expanded="false"
				aria-controls="mainMenuOverflowItems"
			>
				<span class="boxMenuLinkTitle">{lang}wcf.global.button.more{/lang}</span>
				{icon name='chevron-down' type='solid'}
			</button>
			<ol id="mainMenuOverflowItems" class="boxMenuDepth1"></ol>
		</li>
	</ol>

	{if $__wcf->user->isGuest() && $__wcf->getLanguage()->getLanguages()|count > 1}
		<div class="mainMenuMobileOnly mainMenuSettings">
			<button type="button" class="mainMenuSettingsToggle" aria-expanded="false" aria-controls="mainMenuLanguages">
				<span class="mainMenuSettingsLabel">{lang}wcf.user.language{/lang}</span>
				<span class="mainMenuSettingsValue">{$__wcf->getLanguage()}</span>
				{icon name='chevron-down' type='solid'}
			</button>
			<ul id="mainMenuLanguages" class="mainMenuLanguageList" hidden>
				{foreach from=$__wcf->getLanguage()->getLanguages() item=_language}
					<li>
						<button
							type="button"
							class="mainMenuLanguage"
							data-language-id="{$_language->languageID}"
							data-language-code="{$_language->languageCode}"
							lang="{$_language->languageCode}"
							{if $_language->languageID === $__wcf->getLanguage()->languageID} aria-current="true"{/if}
						>
							<img src="{$_language->getIconPath()}" alt="" class="iconFlag">
							{$_language}
						</button>
					</li>
				{/foreach}
			</ul>
		</div>
	{/if}

	{assign var='__footerMenuBox' value=$__wcf->getBoxHandler()->getBoxByIdentifier('com.woltlab.wcf.FooterMenu')}
	{if $__footerMenuBox !== null && $__footerMenuBox->hasContent()}
		<ul class="mainMenuMobileOnly mainMenuFooter">
			{foreach from=$__footerMenuBox->getMenu()->getMenuItemNodeList() item=menuItemNode}
				{if $menuItemNode->getDepth() === 1}
					<li><a {anchorAttributes url=$menuItemNode->getURL()}>{$menuItemNode->getTitle()}</a></li>
				{/if}
			{/foreach}
		</ul>
	{/if}
</nav>
