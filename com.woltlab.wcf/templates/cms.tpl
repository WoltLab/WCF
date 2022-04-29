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

{capture assign='contentInteractionButtons'}
	{if $page->showShareButtons()}
		<a href="{$content->getLink()}" class="contentInteractionButton button small wsShareButton jsOnly" data-link-title="{$content->getTitle()}"><span class="icon icon16 fa-share"></span> <span>{lang}wcf.message.share{/lang}</span></a>
	{/if}

	{if $page->isMultilingual && $__wcf->user->userID && $page->getPageLanguages()|count > 1}
		<div class="contentInteractionButton dropdown jsOnly">
			<a class="button small dropdownToggle boxFlag box24">
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
		</div>
	{/if}

	{if $__wcf->getSession()->getPermission('admin.content.cms.canManagePage')}<a href="{link controller='PageEdit' id=$page->pageID isACP=true}{/link}" class="contentInteractionButton button small"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.page.edit{/lang}</span></a>{/if}
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

{include file='footer'}
