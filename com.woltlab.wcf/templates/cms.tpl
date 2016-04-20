{include file='documentHeader'}

<head>
	<title>{if !$__wcf->isLandingPage()}{$content[title]} - {/if}{PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<link rel="canonical" href="{$canonicalURL}">
</head>

<body id="tpl{$templateName|ucfirst}" data-template="{$templateName}" data-application="{$templateNameApplication}">

{include file='header'}

{if $__wcf->isLandingPage()}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{PAGE_TITLE|language}</h1>
			{hascontent}<p>{content}{PAGE_DESCRIPTION|language}{/content}</p>{/hascontent}
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}{event name='contentHeaderNavigation'}{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{elseif $content[title]}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$content[title]}</h1>
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
