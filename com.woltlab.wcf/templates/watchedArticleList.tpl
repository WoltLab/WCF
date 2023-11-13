{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='WatchedArticleList'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='WatchedArticleList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{capture assign='sidebarRight'}
	{if !$labelGroups|empty}
		<form id="sidebarForm" method="post" action="{link application='wcf' controller=$controllerName object=$controllerObject}{/link}">
			<section class="box">
				<h2 class="boxTitle">{lang}wcf.label.label{/lang}</h2>
				
				<div class="boxContent">
					<dl>
						{include file='__labelPickerGroup'}
					</dl>
					<div class="formSubmit">
						<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
					</div>
				</div>
			</section>
		</form>
	{/if}
{/capture}

{capture assign='contentInteractionPagination'}
	{pages print=true assign='pagesLinks' controller='WatchedArticleList' link="pageNo=%d"}
{/capture}

{capture assign='contentInteractionButtons'}
	<button type="button" class="markAllAsReadButton contentInteractionButton button small jsOnly">{icon name='check'} <span>{lang}wcf.global.button.markAllAsRead{/lang}</span></button>
{/capture}

{include file='header'}

{if $objects|count}
	<div class="section">
		{include file='articleListItems'}
	</div>
{else}
	<p class="info" role="status">{lang}wcf.global.noItems{/lang}</p>
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

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Article/MarkAllAsRead'], ({ setup }) => {
		setup();
	});
</script>

{include file='footer'}
