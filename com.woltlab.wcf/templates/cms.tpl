{if !$__wcf->isLandingPage()}
	{capture assign='pageTitle'}{$content->title}{/capture}
	{capture assign='contentTitle'}{$content->title}{/capture}
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

{capture assign='contentHeaderNavigation'}
	{if $page->isMultilingual && $__wcf->user->userID}
		<li class="dropdown">
			<a class="dropdownToggle boxFlag box24 button">
				<span><img src="{$activePageLanguage->getIconPath()}" alt="" class="iconFlag"></span>
				<span>{$activePageLanguage->languageName}</span>
			</a>
			<ul class="dropdownMenu">
				{foreach from=$page->getPageLanguages() item='pageLanguage'}
					{if $pageLanguage->getLanguage()}
						<li class="boxFlag">
							<a class="box24" href="{$pageLanguage->getLink()}">
								<span><img src="{$pageLanguage->getLanguage()->getIconPath()}" alt="" class="iconFlag"></span>
								<span>{$pageLanguage->getLanguage()->languageName}</span>
							</a>
						</li>
					{/if}
				{/foreach}
			</ul>
		</li>
	{/if}
	
	{if $__wcf->getSession()->getPermission('admin.content.cms.canManagePage')}<li><a href="{link controller='PageEdit' id=$page->pageID isACP=true}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.page.edit{/lang}</span></a></li>{/if}
{/capture}

{include file='header'}

{if $content->content}
	{if $page->pageType == 'text'}
		<section class="section cmsContent htmlContent">
			{@$content->getFormattedContent()}
		</section>
	{elseif $page->pageType == 'html'}
		{@$content->content}
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
