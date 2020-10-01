{if !$__wcf->isLandingPage()}
	{capture assign='pageTitle'}{$content->title}{/capture}
	{capture assign='contentTitle'}{$content->title}{/capture}
{/if}

{capture assign='headContent'}
	{if $page->isMultilingual && $page->getPageLanguages()|count > 1}
		{foreach from=$page->getPageLanguages() item='pageLanguage'}
			<link rel="alternate" hreflang="{$pageLanguage->getLanguage()->languageCode}" href="{$pageLanguage->getLink()}">
		{/foreach}
	{/if}
{/capture}

{capture assign='contentHeaderNavigation'}
	{if $page->isMultilingual && $__wcf->user->userID && $page->getPageLanguages()|count > 1}
		<li class="dropdown">
			<a class="dropdownToggle boxFlag box24 button">
				<span><img src="{$activePageLanguage->getIconPath()}" alt="" class="iconFlag"></span>
				<span>{$activePageLanguage->languageName}</span>
			</a>
			<ul class="dropdownMenu">
				{foreach from=$page->getPageLanguages() item='pageLanguage'}
					<li class="boxFlag">
						<a class="box24" href="{$pageLanguage->getLink()}">
							<span><img src="{$pageLanguage->getLanguage()->getIconPath()}" alt="" class="iconFlag"></span>
							<span>{$pageLanguage->getLanguage()->languageName}</span>
						</a>
					</li>
				{/foreach}
			</ul>
		</li>
	{/if}
	
	{if $__wcf->getSession()->getPermission('admin.content.cms.canManagePage')}<li><a href="{link controller='PageEdit' id=$page->pageID isACP=true}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.page.edit{/lang}</span></a></li>{/if}
{/capture}

{include file='header'}

{if $content->content}
	{if $page->pageType == 'text'}
		<div class="section cmsContent htmlContent">
			{@$content->getFormattedContent()}
		</div>
	{elseif $page->pageType == 'html'}
		{@$content->getParsedContent()}
	{elseif $page->pageType == 'tpl'}
		{@$page->getParsedTemplate($content)}
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

{if $page->showShareButtons()}
	{capture assign='footerBoxes'}
		<section class="box boxFullWidth jsOnly">
			<h2 class="boxTitle">{lang}wcf.message.share{/lang}</h2>
			
			<div class="boxContent">
				{include file='shareButtons'}
			</div>
		</section>
	{/capture}
{/if}

{include file='footer'}
