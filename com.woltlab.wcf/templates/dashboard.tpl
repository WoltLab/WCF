{include file='documentHeader'}

<head>
	<title>{if !$__wcf->isLandingPage()}{lang}wcf.user.dashboard{/lang} - {/if}{PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{link controller='Dashboard'}{/link}" />
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{if $__boxSidebar|isset && $__boxSidebar}
	{capture assign='sidebarRight'}
		{@$__boxSidebar}
	{/capture}
{/if}

{include file='header'}

{if $__wcf->isLandingPage()}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{PAGE_TITLE|language}</h1>
			{hascontent}<p class="contentHeaderDescription">{content}{PAGE_DESCRIPTION|language}{/content}</p>{/hascontent}
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}{event name='contentHeaderNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{else}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{lang}wcf.user.dashboard{/lang}</h1>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}{event name='contentHeaderNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/if}

{include file='userNotice'}

<section id="dashboard">
	{if $__boxContent|isset}{@$__boxContent}{/if}
</section>

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}

</body>
</html>
