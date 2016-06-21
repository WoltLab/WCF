{capture assign='pageTitle'}{if $status == 2}{lang}wcf.moderation.doneItems{/lang}{else}{lang}wcf.moderation.outstandingItems{/lang}{/if}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{if $status == 2}{lang}wcf.moderation.doneItems{/lang}{else}{lang}wcf.moderation.outstandingItems{/lang}{/if} <span class="badge">{#$items}</span>{/capture}

{capture assign='sidebarLeft'}
	<section class="box">
		{* moderation type *}
		<h2 class="boxTitle">{lang}wcf.moderation.filterByType{/lang}</h2>
		
		<nav class="boxContent">
			<ul class="boxMenu">
				<li{if $definitionID == 0} class="active"{/if}><a class="boxMenuLink" href="{link controller='ModerationList'}definitionID=0&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.type.all{/lang}</a></li>
				{foreach from=$availableDefinitions key=__definitionID item=definitionName}
					<li{if $definitionID == $__definitionID} class="active"{/if}><a class="boxMenuLink" href="{link controller='ModerationList'}definitionID={@$__definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.type.{$definitionName}{/lang}</a></li>
				{/foreach}
				
				{event name='sidebarModerationType'}
			</ul>
		</nav>
		
		{* assigned user *}
		<h2 class="boxTitle">{lang}wcf.moderation.filterByUser{/lang}</h2>
		
		<nav class="boxContent">
			<ul class="boxMenu">
				<li{if $assignedUserID == -1} class="active"{/if}><a class="boxMenuLink" href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID=-1&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.filterByUser.allEntries{/lang}</a></li>
				<li{if $assignedUserID == 0} class="active"{/if}><a class="boxMenuLink" href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID=0&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.filterByUser.nobody{/lang}</a></li>
				<li{if $assignedUserID == $__wcf->getUser()->userID} class="active"{/if}><a class="boxMenuLink" href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$__wcf->getUser()->userID}&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.filterByUser.myself{/lang}</a></li>
				
				{event name='sidebarAssignedUser'}
			</ul>
		</nav>
		
		{* status *}
		<h2 class="boxTitle">{lang}wcf.moderation.status{/lang}</h2>
		
		<nav class="boxContent">
			<ul class="boxMenu">
				<li{if $status == -1} class="active"{/if}><a class="boxMenuLink" href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status=-1&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.status.outstanding{/lang}</a></li>
				<li{if $status == 2} class="active"{/if}><a class="boxMenuLink" href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status=2&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.status.done{/lang}</a></li>
				
				{event name='sidebarStatus'}
			</ul>
		</nav>
	</section>
	
	{event name='sidebarBoxes'}
{/capture}

{capture assign='headerNavigation'}
	<li class="jsOnly"><a href="#" title="{lang}wcf.moderation.markAllAsRead{/lang}" class="markAllAsReadButton jsTooltip"><span class="icon icon16 fa-check"></span> <span class="invisible">{lang}wcf.moderation.markAllAsRead{/lang}</span></a></li>
{/capture}

{include file='header'}

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='ModerationList' link="definitionID=$definitionID&assignedUserID=$assignedUserID&status=$status&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox messageGroupList moderationList">
		<table class="table">
			<thead>
				<tr>
					<th class="columnText columnTitle" colspan="2">{lang}wcf.moderation.title{/lang}</th>
					<th class="columnText columnAssignedUserID{if $sortField == 'assignedUsername'} active {@$sortOrder}{/if}"><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField=assignedUsername&sortOrder={if $sortField == 'assignedUsername' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.moderation.assignedUser{/lang}</a></th>
					<th class="columnDigits columnComments{if $sortField == 'comments'} active {@$sortOrder}{/if}"><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField=comments&sortOrder={if $sortField == 'comments' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.comments{/lang}</a></th>
					<th class="columnDate columnLastChangeTime{if $sortField == 'lastChangeTime'} active {@$sortOrder}{/if}"><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField=lastChangeTime&sortOrder={if $sortField == 'lastChangeTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.moderation.lastChangeTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=entry}
					<tr class="moderationQueueEntry{if $entry->isNew()} new{/if}" data-queue-id="{@$entry->queueID}">
						<td class="columnIcon columnAvatar">
							<div>
								<p{if $entry->isNew()} title="{lang}wcf.moderation.markAsRead.doubleClick{/lang}"{/if}>{@$entry->getUserProfile()->getAvatar()->getImageTag(48)}</p>
							</div>
						</td>
						<td class="columnText columnSubject">
							<h3>
								<span class="badge label">{lang}wcf.moderation.type.{@$definitionNames[$entry->objectTypeID]}{/lang}</span>
								<a href="{$entry->getLink()}" class="messageGroupLink">{$entry->getTitle()|tableWordwrap}</a>
							</h3>
							
							<ul class="inlineList dotSeparated small messageGroupInfo">
								<li class="messageGroupAuthor">{if $entry->getAffectedObject()->getUserID()}<a href="{link controller='User' id=$entry->getAffectedObject()->getUserID()}{/link}" class="userLink" data-user-id="{@$entry->getAffectedObject()->getUserID()}">{$entry->getAffectedObject()->getUsername()}</a>{else}{$entry->getAffectedObject()->getUsername()}{/if}</li>
								<li class="messageGroupTime">{@$entry->getAffectedObject()->getTime()|time}</li>
								<li>{lang}wcf.moderation.type.{@$entry->getObjectTypeName()}{/lang}</li>
								
								{event name='messageGroupInfo'}
							</ul>
						</td>
						<td class="columnText columnAssignedUserID">{if $entry->assignedUserID}<a href="{link controller='User' id=$entry->assignedUserID}{/link}" class="userLink" data-user-id="{@$entry->assignedUserID}">{$entry->assignedUsername}</a>{/if}</td>
						<td class="columnDigits columnComments">{#$entry->comments}</td>
						<td class="columnDate columnLastChangeTime">{if $entry->lastChangeTime}{@$entry->lastChangeTime|time}{/if}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
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
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Moderation.Queue.MarkAsRead();
		new WCF.Moderation.Queue.MarkAllAsRead();
	});
	//]]>
</script>

{include file='footer'}
