{capture assign='pageTitle'}{lang}wcf.tagging.taggedObjects.{@$objectType}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.tagging.taggedObjects.{@$objectType}{/lang}{/capture}

{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='Tagged' object=$tag}objectType={@$objectType}&pageNo={@$pageNo+1}{/link}" />
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='Tagged' object=$tag}objectType={@$objectType}{if $pageNo > 2}&pageNo={@$pageNo-1}{/if}{/link}" />
	{/if}
	<link rel="canonical" href="{link controller='Tagged' object=$tag}objectType={@$objectType}{if $pageNo > 1}&pageNo={@$pageNo}{/if}{/link}" />
{/capture}

{capture assign='sidebarLeft'}
	<section class="box">
		<h2 class="boxTitle">{lang}wcf.tagging.objectTypes{/lang}</h2>
		
		<nav class="boxContent">
			<ul class="boxMenu">
				{foreach from=$availableObjectTypes item=availableObjectType}
					<li{if $objectType == $availableObjectType->objectType} class="active"{/if}><a class="boxMenuLink" href="{link controller='Tagged' object=$tag}objectType={@$availableObjectType->objectType}{/link}">{lang}wcf.tagging.objectType.{@$availableObjectType->objectType}{/lang}</a></li>
				{/foreach}
			</ul>
		</nav>
	</section>
	
	<section class="box">
		<h2 class="boxTitle">{lang}wcf.tagging.tags{/lang}</h2>
		
		<div class="boxContent">
			{include file='tagCloudBox' taggableObjectType=$objectType}
		</div>
	</section>
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='Tagged' object=$tag link="objectType=$objectType&pageNo=%d"}{/content}
	</div>
{/hascontent}

{if $items}
	{include file=$resultListTemplateName application=$resultListApplication}
{else}
	<p class="info">{lang}wcf.tagging.taggedObjects.noResults{/lang}</p>
{/if}

<footer class="contentFooter">
	{hascontent}
		<div class="paginationBottom">
			{content}{@$pagesLinks}{/content}
		</div>
	{/hascontent}
	
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{include file='footer'}
