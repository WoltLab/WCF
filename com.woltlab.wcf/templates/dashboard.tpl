{include file='documentHeader'}

<head>
	<title>{if $__wcf->getPageMenu()->getLandingPage()->menuItem != 'wcf.user.dashboard'}{lang}wcf.user.dashboard{/lang} - {/if}{PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{link controller='Dashboard'}{/link}" />
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{if $__boxSidebar|isset && $__boxSidebar}
	{capture assign='sidebar'}
		{@$__boxSidebar}
	{/capture}
{/if}

{include file='header' sidebarOrientation='right'}

{if $__wcf->getPageMenu()->getLandingPage()->menuItem == 'wcf.user.dashboard'}
	<header class="boxHeadline">
		<h1>{PAGE_TITLE|language}</h1>
		{hascontent}<p>{content}{PAGE_DESCRIPTION|language}{/content}</p>{/hascontent}
	</header>
{else}
	<header class="boxHeadline">
		<h1>{lang}wcf.user.dashboard{/lang}</h1>
	</header>
{/if}

{include file='userNotice'}

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<section id="dashboard">
	{if $__boxContent|isset}{@$__boxContent}{/if}
</section>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}

</body>
</html>
