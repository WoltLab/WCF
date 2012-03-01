<a id="top"></a>
<!-- HEADER -->
<header id="pageHeader" class="wcf-pageHeader">
	<div>
		{hascontent}
			<!-- top menu -->
			<nav id="topMenu" class="wcf-topMenu">
				<div>
					<ul>
						{content}{event name='topMenu'}{/content}
					</ul>
				</div>
			</nav>
			<!-- /top menu -->
		{/hascontent}
		
		<!-- logo -->
		<div id="logo" class="wcf-logo">
			<!-- clickable area -->
			<a href="{link controller='Index'}{/link}">
				{event name='headerLogo'}
			</a>
			<!-- /clickable area -->
			
			<!-- search area -->
			{event name='searchArea'}
			<!-- /search area -->
		</div>
		<!-- /logo -->
		
		<!-- main menu -->
		{include file='mainMenu'}
		<!-- /main menu -->
		
		<!-- header navigation -->
		<nav class="wcf-headerNavigation">
			<!-- sub menu -->
			{include file='mainMenuSubMenu'}
			
			<ul>
				<li id="toBottomLink" class="wcf-toBottomLink"><a href="{$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><img src="{icon size='S'}toBottom{/icon}" alt="" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				{event name='headerNavigation'}
			</ul>
		</nav>
		<!-- /header navigation -->
	</div>
</header>
<!-- /HEADER -->

<!-- MAIN -->
<div id="main" class="wcf-main{if $sidebarOrientation|isset} {@$sidebarOrientation}{/if}">
	<div>
		{if $sidebar|isset}
			<aside class="wcf-sidebar">
				{@$sidebar}
			</aside>
		{/if}
				
		<!-- CONTENT -->
		<section id="content" class="wcf-content">
			
			{if $skipBreadcrumbs|empty}{include file='breadcrumbs' sandbox=false}{/if}
			