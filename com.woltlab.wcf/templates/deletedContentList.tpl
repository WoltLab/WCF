{capture assign='pageTitle'}{lang}wcf.moderation.deletedContent.{$objectType}{/lang}{/capture}

{capture assign='sidebarRight'}
	<section class="box" data-static-box-identifier="com.woltlab.wcf.DeletedContentListMenu">
		<h2 class="boxTitle">{lang}wcf.moderation.deletedContent.objectTypes{/lang}</h2>
		
		<div class="boxContent">
			<ul class="boxMenu">
				{foreach from=$providerLinks item=providerLink}
					<li{if $objectType === $providerLink[identifier]} class="active"{/if}>
						<a class="boxMenuLink" href="{$providerLink[link]}">
							<span class="boxMenuLinkTitle">{$providerLink[title]}</span>
						</a>
					</li>
				{/foreach}
			</ul>
		</div>
	</section>
{/capture}

{capture assign='contentTitle'}{lang}wcf.moderation.deletedContent.{$objectType}{/lang}{/capture}

{capture assign='contentInteractionPagination'}
	{if $pages > 1}
		<woltlab-core-pagination
			page="{$pageNo}"
			count="{$pages}"
			url="{link controller='DeletedContentList' objectType=$objectType}{/link}"
		></woltlab-core-pagination>
	{/if}
{/capture}

{include file='header'}

{if $items}
	{include file=$resultListTemplateName application=$resultListApplication}
{else}
	<woltlab-core-notice type="info">{lang}wcf.global.noItems{/lang}</woltlab-core-notice>
{/if}

<footer class="contentFooter">
	{if $pages > 1}
		<div class="paginationBottom">
			<woltlab-core-pagination
				page="{$pageNo}"
				count="{$pages}"
				url="{link controller='DeletedContentList' objectType=$objectType}{/link}"
			></woltlab-core-pagination>
		</div>
	{/if}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
