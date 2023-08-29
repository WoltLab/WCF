{include file='documentHeader'}

<head>
	<meta charset="utf-8">
	{if !$pageTitle|isset}
		{assign var='pageTitle' value=''}
		{if (!$__wcf->isLandingPage() || !USE_PAGE_TITLE_ON_LANDING_PAGE) && $__wcf->getActivePage() != null && $__wcf->getActivePage()->getTitle()}
			{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}{/capture}
		{/if}
	{/if}
	
	<title>{if $pageTitle}{@$pageTitle} - {/if}{PAGE_TITLE|phrase}</title>
	
	{include file='headInclude'}
	
	{if !$canonicalURL|empty}
		<link rel="canonical" href="{$canonicalURL}">
	{/if}
	
	{if !$headContent|empty}
		{@$headContent}
	{/if}
</head>

<body id="tpl_{$templateNameApplication}_{$templateName}"
	itemscope itemtype="http://schema.org/WebPage"{if !$canonicalURL|empty} itemid="{$canonicalURL}"{/if}
	data-template="{$templateName}" data-application="{$templateNameApplication}"{if $__wcf->getActivePage() != null} data-page-id="{@$__wcf->getActivePage()->pageID}" data-page-identifier="{$__wcf->getActivePage()->identifier}"{/if}
	{if !$__pageDataAttributes|empty}{@$__pageDataAttributes}{/if}
	class="{if $__wcf->getActivePage() != null && $__wcf->getActivePage()->cssClassName}{$__wcf->getActivePage()->cssClassName}{/if}{if !$__pageCssClassName|empty} {$__pageCssClassName}{/if}">

<span id="top"></span>

<div id="pageContainer" class="pageContainer">
	{event name='beforePageHeader'}
	
	{include file='pageHeader'}
	
	{event name='afterPageHeader'}
	
	{hascontent}
		<div class="boxesHeaderBoxes">
			<div class="layoutBoundary">
				<div class="boxContainer">
					{content}
						{if !$headerBoxes|empty}
							{@$headerBoxes}
						{/if}
						
						{foreach from=$__wcf->getBoxHandler()->getBoxes('headerBoxes') item=box}
							{@$box->render()}
						{/foreach}
					{/content}
				</div>
			</div>
		</div>
	{/hascontent}
	
	{include file='pageNavbarTop'}
	
	{hascontent}
		<div class="boxesTop">
			<div class="boxContainer">
				{content}
					{if !$boxesTop|empty}
						{@$boxesTop}
					{/if}
				
					{foreach from=$__wcf->getBoxHandler()->getBoxes('top') item=box}
						{@$box->render()}
					{/foreach}
				{/content}
			</div>
		</div>
	{/hascontent}
	
	<section id="main" class="main" role="main"{if !$__mainItemScope|empty} {@$__mainItemScope}{/if}>
		<div class="layoutBoundary">
			{hascontent}
				{if !$__sidebarLeftShow|isset}{assign var='__sidebarLeftShow' value='wcf.global.button.showSidebarLeft'|phrase}{/if}
				{if !$__sidebarLeftHide|isset}{assign var='__sidebarLeftHide' value='wcf.global.button.hideSidebar'|phrase}{/if}
				
				<aside class="sidebar boxesSidebarLeft{if !$__sidebarLeftHasMenu|empty || $__wcf->getBoxHandler()->sidebarLeftHasMenu()} boxesSidebarLeftHasMenu{/if}" aria-label="{lang}wcf.page.sidebar.left{/lang}" data-show-sidebar="{$__sidebarLeftShow}" data-hide-sidebar="{$__sidebarLeftHide}" data-show-navigation="{lang}wcf.global.button.showNavigation{/lang}" data-hide-navigation="{lang}wcf.global.button.hideNavigation{/lang}">
					<div class="boxContainer">
						{content}
							{event name='boxesSidebarLeftTop'}
							
							{* WCF2.1 Fallback *}
							{if !$sidebar|empty}
								{if !$sidebarOrientation|isset || $sidebarOrientation == 'left'}
									{@$sidebar}
								{/if}
							{/if}
							
							{if !$sidebarLeft|empty}
								{@$sidebarLeft}
							{/if}
							
							{foreach from=$__wcf->getBoxHandler()->getBoxes('sidebarLeft') item=box}
								{@$box->render()}
							{/foreach}
							
							{event name='boxesSidebarLeftBottom'}
						{/content}
					</div>
				</aside>
			{/hascontent}

			{capture assign='__sidebarRightContent'}
				{if MODULE_WCF_AD && $__disableAds|empty && $__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}
					<div class="box boxBorderless">
						<div class="boxContent">
							{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.top')}
						</div>
					</div>
				{/if}
				
				{event name='boxesSidebarRightTop'}
				
				{* WCF2.1 Fallback *}
				{if !$sidebar|empty}
					{if !$sidebarOrientation|isset || $sidebarOrientation == 'right'}
						{@$sidebar}
					{/if}
				{/if}
				
				{if !$sidebarRight|empty}
					{@$sidebarRight}
				{/if}
				
				{foreach from=$__wcf->getBoxHandler()->getBoxes('sidebarRight') item=box}
					{@$box->render()}
				{/foreach}
				
				{event name='boxesSidebarRightBottom'}

				{if MODULE_WCF_AD && $__disableAds|empty && $__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}
					<div class="box boxBorderless">
						<div class="boxContent">
							{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.sidebar.bottom')}
						</div>
					</div>
				{/if}
			{/capture}
			
			<div id="content" class="content{if $__sidebarRightContent|trim} content--sidebar-right{/if}">
				{if MODULE_WCF_AD && $__disableAds|empty}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.header.content')}{/if}
				
				{if $__disableContentHeader|empty}
					{if !$contentHeader|empty}
						{@$contentHeader}
					{else}
						{if $contentTitle|empty}
							{if $__wcf->isLandingPage() && USE_PAGE_TITLE_ON_LANDING_PAGE}
								{capture assign='contentTitle'}{PAGE_TITLE|phrase}{/capture}
								{capture assign='contentDescription'}{PAGE_DESCRIPTION|phrase}{/capture}
							{elseif $__wcf->getActivePage() != null && $__wcf->getActivePage()->getTitle()}
								{capture assign='contentTitle'}{$__wcf->getActivePage()->getTitle()}{/capture}
							{/if}
						{/if}
						
						{if !$contentTitle|empty}
							<header class="contentHeader">
								<div class="contentHeaderTitle">
									<h1 class="contentTitle">{@$contentTitle}{if !$contentTitleBadge|empty} {@$contentTitleBadge}{/if}</h1>
									{if !$contentDescription|empty}<p class="contentHeaderDescription">{@$contentDescription}</p>{/if}
								</div>
								
								{hascontent}
									<nav class="contentHeaderNavigation">
										<ul>
											{content}
												{if !$contentHeaderNavigation|empty}{@$contentHeaderNavigation}{/if}
												
												{event name='contentHeaderNavigation'}
											{/content}
										</ul>
									</nav>
								{/hascontent}
							</header>
						{/if}
					{/if}
				{/if}
				
				{include file='userNotice'}
				
				{hascontent}
					<div class="boxesContentTop">
						<div class="boxContainer">
							{content}
								{if !$boxesContentTop|empty}
									{@$boxesContentTop}
								{/if}
								
								{foreach from=$__wcf->getBoxHandler()->getBoxes('contentTop') item=box}
									{@$box->render()}
								{/foreach}
							{/content}
						</div>
					</div>
				{/hascontent}
				
				{event name='contents'}

				{include file='contentInteraction'}
