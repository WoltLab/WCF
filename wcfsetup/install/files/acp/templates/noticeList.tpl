{include file='header' pageTitle='wcf.acp.notice.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\notice\\NoticeAction', '.jsNotice');
		new WCF.Action.Toggle('wcf\\data\\notice\\NoticeAction', '.jsNotice');
		new WCF.Sortable.List('noticeList', 'wcf\\data\\notice\\NoticeAction', {@$startIndex});
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.notice.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="NoticeList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='NoticeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.notice.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if $objects|count}
	<div class="container containerPadding sortableListContainer marginTop" id="noticeList">
		<ol class="sortableList" data-object-id="0" start="{@($pageNo - 1) * $itemsPerPage + 1}">
			{foreach from=$objects item='notice'}
				<li class="sortableNode sortableNoNesting jsNotice" data-object-id="{@$notice->noticeID}">
					<span class="sortableNodeLabel">
						<a href="{link controller='NoticeEdit' object=$notice}{/link}">{$notice->noticeName}</a>
						
						<span class="statusDisplay sortableButtonContainer">
							<span class="icon icon16 fa-{if $notice->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $notice->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$notice->noticeID}"></span>
							<a href="{link controller='NoticeEdit' object=$notice}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$notice->noticeID}" data-confirm-message="{lang}wcf.acp.notice.delete.confirmMessage{/lang}"></span>
							
							{event name='itemButtons'}
						</span>
					</span>
				</li>
			{/foreach}
		</ol>
		
		<div class="formSubmit">
			<button class="button" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
		</div>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='NoticeAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.notice.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
