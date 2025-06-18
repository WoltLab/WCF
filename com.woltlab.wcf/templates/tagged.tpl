{capture assign='pageTitle'}{lang}wcf.tagging.taggedObjects.{$objectType}{/lang}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{lang}wcf.tagging.taggedObjects.{$objectType}{/lang}{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='Tagged' object=$tag objectType=$objectType pageNo=$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='Tagged' object=$tag objectType=$objectType}{if $pageNo > 2}&pageNo={$pageNo-1}{/if}{/link}">
	{/if}
	<link rel="canonical" href="{link controller='Tagged' object=$tag objectType=$objectType}{if $pageNo > 1}&pageNo={$pageNo}{/if}{/link}">
{/capture}

{capture assign='sidebarRight'}
	<section class="box" data-static-box-identifier="com.woltlab.wcf.TaggedMenu">
		<h2 class="boxTitle">{lang}wcf.tagging.objectTypes{/lang}</h2>
		
		<nav class="boxContent">
			<ul class="boxMenu">
				{foreach from=$objectTypeLinks item=objectTypeLink}
					<li{if $objectType == $objectTypeLink[objectType]} class="active"{/if}>
						<a class="boxMenuLink" href="{$objectTypeLink[link]}">
							<span class="boxMenuLinkTitle">{$objectTypeLink[title]}</span>
							<span class="badge">{$objectTypeLink[items]}</span>
						</a>
					</li>
				{/foreach}
			</ul>
		</nav>
	</section>
	
	{if !$tags|empty}
		<section class="box" data-static-box-identifier="com.woltlab.wcf.TaggedTagCloud">
			<h2 class="boxTitle">{lang}wcf.tagging.tags{/lang}</h2>
			
			<div class="boxContent">
				{include file='tagCloudBox' taggableObjectType=$objectType}
			</div>
		</section>
	{/if}
{/capture}

{capture assign='contentInteractionPagination'}
	{if $pages > 1}
		<woltlab-core-pagination
			page="{$pageNo}"
			count="{$pages}"
			url="{link controller='Tagged' object=$tag objectType=$objectType}{/link}"
		></woltlab-core-pagination>
	{/if}
{/capture}

{capture assign='contentInteractionButtons'}
	<a href="{link controller='TagSearch'}{/link}" class="contentInteractionButton button small">{icon name='magnifying-glass'} <span>{lang}wcf.search.type.tags{/lang}</span></a>
{/capture}

{include file='header'}

{if $items}
	{include file=$resultListTemplateName application=$resultListApplication}
{else}
	<woltlab-core-notice type="info">{lang}wcf.tagging.taggedObjects.noResults{/lang}</woltlab-core-notice>
{/if}

<footer class="contentFooter">
	{if $pages > 1}
		<div class="paginationBottom">
			<woltlab-core-pagination
				page="{$pageNo}"
				count="{$pages}"
				url="{link controller='Tagged' object=$tag objectType=$objectType}{/link}"
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
