<a id="top"></a>
<!-- HEADER -->
<header id="pageHeader" class="pageHeader">
	<div>
		{hascontent}
			<!-- top menu -->
			<nav id="topMenu" class="topMenu">
				<div>
					<ul>
						{content}{event name='topMenu'}{/content}
					</ul>
				</div>
			</nav>
			<!-- /top menu -->
		{/hascontent}
		
		<!-- logo -->
		<div id="logo" class="logo">
			<!-- clickable area -->
			<a href="{link controller='Index'}{/link}">
				<!-- *** insert header logo here -->
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
		<nav class="headerNavigation">
			<div>
				<ul>
					<li id="toBottomLink" class="toBottomLink"><a href="#bottom" title="{lang}wcf.global.scrollDown{/lang}" class="balloonTooltip"><img src="{icon size='S'}toBottom{/icon}" alt="" /> <span class="invisible">{lang}wcf.global.scrollDown{/lang}</span></a></li>
					{event name='headerNavigation'}
				</ul>
			</div>
		</nav>
		<!-- /header navigation -->
	</div>
</header>
<!-- /HEADER -->

<!-- MAIN -->
<div id="main" class="main">
	<div>
		
		<!-- CONTENT -->
		<section id="content" class="content">
			
			{include file='breadcrumbs' sandbox=false}
			