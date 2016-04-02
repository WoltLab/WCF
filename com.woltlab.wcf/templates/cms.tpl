{include file='documentHeader'}

<head>
	<title>{if !$page->isLandingPage}{$content[title]} - {/if}{PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{$canonicalURL}">
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header'}

{if $page->isLandingPage}
	<header class="contentHeader">
		<h1 class="contentTitle">{PAGE_TITLE|language}</h1>
		{hascontent}<p>{content}{PAGE_DESCRIPTION|language}{/content}</p>{/hascontent}
	</header>
{else}
	{if $content[title]}
		<header class="contentHeader">
			<h1 class="contentTitle">{$content[title]}</h1>
		</header>
	{/if}	
{/if}

{include file='userNotice'}

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	</div>
{/hascontent}

{if $content[content]}
	{if $page->pageType == 'text'}
		<section class="section cmsContent htmlContent">
			{@$content[content]}
		</section>
	{elseif $page->pageType == 'html'}
		{@$content[content]}
	{elseif $page->pageType == 'tpl'}
		{*todo*}
	{/if}
{/if}

{hascontent}
	<div class="contentNavigation">
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	</div>
{/hascontent}

{include file='footer'}

</body>
</html>
