{capture assign='headContent'}
	{if $pageNo < $pages}
		<link rel="next" href="{link controller='WatchedArticleList'}pageNo={@$pageNo+1}{/link}">
	{/if}
	{if $pageNo > 1}
		<link rel="prev" href="{link controller='WatchedArticleList'}{if $pageNo > 2}pageNo={@$pageNo-1}{/if}{/link}">
	{/if}
{/capture}

{capture assign='headerNavigation'}>
	{if ARTICLE_ENABLE_VISIT_TRACKING}
		<li class="jsOnly"><a href="#" title="{lang}wcf.article.markAllAsRead{/lang}" class="markAllAsReadButton jsTooltip"><span class="icon icon16 fa-check"></span> <span class="invisible">{lang}wcf.article.markAllAsRead{/lang}</span></a></li>
	{/if}
{/capture}

{capture assign='sidebarRight'}
	{if !$labelGroups|empty}
		<form id="sidebarForm" method="post" action="{link application='wcf' controller=$controllerName object=$controllerObject}{/link}">
			<section class="box">
				<h2 class="boxTitle">{lang}wcf.label.label{/lang}</h2>
				
				<div class="boxContent">
					<dl>
						{foreach from=$labelGroups item=labelGroup}
							{if $labelGroup|count}
								<dt>{$labelGroup->getTitle()}</dt>
								<dd>
									<ul class="labelList jsOnly">
										<li class="dropdown labelChooser" id="labelGroup{@$labelGroup->groupID}" data-group-id="{@$labelGroup->groupID}">
											<div class="dropdownToggle" data-toggle="labelGroup{@$labelGroup->groupID}"><span class="badge label">{lang}wcf.label.none{/lang}</span></div>
											<div class="dropdownMenu">
												<ul class="scrollableDropdownMenu">
													{foreach from=$labelGroup item=label}
														<li data-label-id="{@$label->labelID}"><span><span class="badge label{if $label->getClassNames()} {@$label->getClassNames()}{/if}">{$label->getTitle()}</span></span></li>
													{/foreach}
												</ul>
											</div>
										</li>
									</ul>
									<noscript>
										{foreach from=$labelGroups item=labelGroup}
											<select name="labelIDs[{@$labelGroup->groupID}]">
												<option value="0">{lang}wcf.label.none{/lang}</option>
												<option value="-1">{lang}wcf.label.withoutSelection{/lang}</option>
												{foreach from=$labelGroup item=label}
													<option value="{@$label->labelID}"{if $labelIDs[$labelGroup->groupID]|isset && $labelIDs[$labelGroup->groupID] == $label->labelID} selected{/if}>{$label->getTitle()}</option>
												{/foreach}
											</select>
										{/foreach}
									</noscript>
								</dd>
							{/if}
						{/foreach}
					</dl>
					<div class="formSubmit">
						<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
					</div>
				</div>
			</section>
		</form>
		
		<script data-relocate="true">
			$(function() {
				WCF.Language.addObject({
					'wcf.label.none': '{lang}wcf.label.none{/lang}',
					'wcf.label.withoutSelection': '{lang}wcf.label.withoutSelection{/lang}'
				});
				
				new WCF.Label.Chooser({ {implode from=$labelIDs key=groupID item=labelID}{@$groupID}: {@$labelID}{/implode} }, '#sidebarForm', undefined, true);
			});
		</script>
	{/if}
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign='pagesLinks' controller='WatchedArticleList' link="pageNo=%d"}
		{/content}
	</div>
{/hascontent}

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

{if ARTICLE_ENABLE_VISIT_TRACKING}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Article/MarkAllAsRead'], function(UiArticleMarkAllAsRead) {
			UiArticleMarkAllAsRead.init();
		});
	</script>
{/if}

{include file='footer'}
