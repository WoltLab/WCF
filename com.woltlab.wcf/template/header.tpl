<a id="top"></a>
<!-- HEADER -->
<header id="pageHeader" class="layoutFluid">
	<div>
		{hascontent}
			<!-- top menu -->
			<nav id="topMenu" class="userPanel">
				<div class="layoutFluid clearfix">
					<ul class="userPanelItems">
						{content}{event name='topMenu'}{/content}
					</ul>
					
					<!-- search area -->
					{event name='searchArea'}
					<!-- /search area -->
				</div>
			</nav>
			<!-- /top menu -->
		{/hascontent}
		
		<!-- logo -->
		<div id="logo" class="logo">
			<!-- clickable area -->
			<a href="{link controller='Index'}{/link}">
				<img src="{@$__wcf->getPath('wbb')}images/wbbLogo2.svg" alt="" style="height: 80px; width: 300px;" />
				{*event name='headerLogo'*}
			</a>
			<!-- /clickable area -->
		</div>
		<!-- /logo -->
		
		<!-- main menu -->
		{include file='mainMenu'}
		<!-- /main menu -->
		
		<!-- header navigation -->
		<nav class="navigation navigationHeader clearfix">
			<!-- sub menu -->
			{include file='mainMenuSubMenu'}
			
			<ul class="navigationIcons">
				<li id="toBottomLink"><a href="{$__wcf->getAnchor('bottom')}" title="{lang}wcf.global.scrollDown{/lang}" class="jsTooltip"><img src="{icon size='S'}toBottom{/icon}" alt="" class="icon16" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
				{if $headerNavigation|isset}{@$headerNavigation}{/if}
				{event name='headerNavigation'}
			</ul>
		</nav>
		<!-- /header navigation -->
	</div>
</header>
<!-- /HEADER -->

<!-- MAIN -->
<div id="main" class="layoutFluid{if $sidebarOrientation|isset} sidebarOrientation{@$sidebarOrientation|ucfirst} clearfix{/if}">
	<div>
		{if $sidebar|isset}
			<aside class="sidebar">
				{@$sidebar}
			</aside>
		{/if}
				
		<!-- CONTENT -->
		<section id="content" class="content">
			
			{if $skipBreadcrumbs|empty}{include file='breadcrumbs' sandbox=false}{/if}
			