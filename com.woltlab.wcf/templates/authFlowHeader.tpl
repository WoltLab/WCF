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
	class="authFlow{if $__wcf->getActivePage() != null && $__wcf->getActivePage()->cssClassName} {$__wcf->getActivePage()->cssClassName}{/if}{if !$__pageCssClassName|empty} {$__pageCssClassName}{/if}">

<span id="top"></span>

<div id="pageContainer" class="pageContainer">
	<div id="pageHeaderContainer" class="pageHeaderContainer">
		<header id="pageHeader" class="pageHeader pageHeader--authFlow">
			<div id="pageHeaderPanel" class="pageHeaderPanel">
				<div class="layoutBoundary">
					{include file='pageHeaderMenu'}
					
					{include file='pageHeaderUser'}
				</div>
			</div>
			
			<div id="pageHeaderFacade" class="pageHeaderFacade">
				<div class="layoutBoundary">
					{include file='pageHeaderLogo'}
				</div>
			</div>
		</header>
	</div>
	
	<section id="main" class="main" role="main"{if !$__mainItemScope|empty} {@$__mainItemScope}{/if}>
		<div class="layoutBoundary">
			<div id="content" class="content">
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
							</header>
						{/if}
					{/if}
				{/if}
				
				{include file='userNotice'}
