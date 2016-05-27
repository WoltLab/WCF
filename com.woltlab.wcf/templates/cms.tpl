{capture assign='headContent'}
	<link rel="canonical" href="{$canonicalURL}">
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
