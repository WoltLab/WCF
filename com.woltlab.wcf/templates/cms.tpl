{if !$__wcf->isLandingPage()}
	{capture assign='pageTitle'}{$content[title]}{/capture}
	{capture assign='contentTitle'}{$content[title]}{/capture}
{/if}

{capture assign='headContent'}
	{if $page->isMultilingual}
		{foreach from=$page->getPageLanguages() item='pageLanguage'}
			{if $pageLanguage->getLanguage()}
				<link rel="alternate" hreflang="{$pageLanguage->getLanguage()->languageCode}" href="{$pageLanguage->getLink()}">
			{/if}
		{/foreach}
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
