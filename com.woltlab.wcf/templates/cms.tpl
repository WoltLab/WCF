{if !$__wcf->isLandingPage()}
	{capture assign='pageTitle'}{$content[title]}{/capture}
{/if}

{capture assign='headContent'}
	<link rel="canonical" href="{$canonicalURL}">
{/capture}

{capture assign='contentHeader'}
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
{/capture}

{include file='header'}

{if $content[content]}
	{if $page->pageType == 'text'}
		<section class="section cmsContent htmlContent">
			{@$content[content]}
		</section>
	{elseif $page->pageType == 'html'}
		{@$content[content]}
	{elseif $page->pageType == 'tpl'}
		{include file=$page->getTplName($contentLanguageID)}
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
