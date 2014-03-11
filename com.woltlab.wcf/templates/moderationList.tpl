{include file='documentHeader'}

<head>
	<title>{lang}wcf.moderation.moderation{/lang} {if $pageNo > 1}- {lang}wcf.page.pageNo{/lang} {/if}- {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
</head>

<body id="tpl{$templateName|ucfirst}">

{capture assign='sidebar'}
	{* moderation type *}
	<fieldset>
		<legend>{lang}wcf.moderation.filterByType{/lang}</legend>
		
		<nav>
			<ul>
				<li{if $definitionID == 0} class="active"{/if}><a href="{link controller='ModerationList'}definitionID=0&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.type.all{/lang}</a></li>
				{foreach from=$availableDefinitions key=__definitionID item=definitionName}
					<li{if $definitionID == $__definitionID} class="active"{/if}><a href="{link controller='ModerationList'}definitionID={@$__definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.type.{$definitionName}{/lang}</a></li>
				{/foreach}
				
				{event name='sidebarModerationType'}
			</ul>
		</nav>
	</fieldset>
	
	{* assigned user *}
	<fieldset>
		<legend>{lang}wcf.moderation.filterByUser{/lang}</legend>
		
		<nav>
			<ul>
				<li{if $assignedUserID == -1} class="active"{/if}><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID=-1&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.filterByUser.allEntries{/lang}</a></li>
				<li{if $assignedUserID == 0} class="active"{/if}><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID=0&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.filterByUser.nobody{/lang}</a></li>
				<li{if $assignedUserID == $__wcf->getUser()->userID} class="active"{/if}><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$__wcf->getUser()->userID}&status={@$status}&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.filterByUser.myself{/lang}</a></li>
				
				{event name='sidebarAssignedUser'}
			</ul>
		</nav>
	</fieldset>
	
	{* status *}
	<fieldset>
		<legend>{lang}wcf.moderation.status{/lang}</legend>
		
		<nav>
			<ul>
				<li{if $status == -1} class="active"{/if}><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status=-1&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.status.all{/lang}</a></li>
				<li{if $status == 2} class="active"{/if}><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status=2&pageNo={@$pageNo}&sortField={@$sortField}&sortOrder={@$sortOrder}{/link}">{lang}wcf.moderation.status.done{/lang}</a></li>
				
				{event name='sidebarStatus'}
			</ul>
		</nav>
	</fieldset>
	
	{event name='sidebarBoxes'}
{/capture}

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.moderation.moderation{/lang}</h1>
</header>

{include file='userNotice'}

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='ModerationList' link="id=$definitionID&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $objects|count}
	<div class="marginTop tabularBox tabularBoxTitle messageGroupList moderationList">
		<header>
			<h2>{if $status == 2}{lang}wcf.moderation.doneItems{/lang}{else}{lang}wcf.moderation.outstandingItems{/lang}{/if} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID{if $sortField == 'queueID'} active {@$sortOrder}{/if}"><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField=queueID&sortOrder={if $sortField == 'queueID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnTitle" colspan="2">{lang}wcf.moderation.title{/lang}</th>
					<th class="columnText columnAssignedUserID{if $sortField == 'assignedUsername'} active {@$sortOrder}{/if}"><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField=assignedUsername&sortOrder={if $sortField == 'assignedUsername' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.moderation.assignedUser{/lang}</a></th>
					<th class="columnDate columnLastChangeTime{if $sortField == 'lastChangeTime'} active {@$sortOrder}{/if}"><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField=lastChangeTime&sortOrder={if $sortField == 'lastChangeTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.moderation.lastChangeTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=entry}
					<tr>
						<td class="columnID">{#$entry->queueID}</td>
						<td class="columnIcon"><p class="framed">{@$entry->getUserProfile()->getAvatar()->getImageTag(32)}</p></td>
						<td class="columnText columnSubject">
							<h3>
								<span class="badge label">{lang}wcf.moderation.type.{@$definitionNames[$entry->objectTypeID]}{/lang}</span>
								<a href="{$entry->getLink()}" class="messageGroupLink">{$entry->getTitle()|tableWordwrap}</a>
							</h3>
							<small>{if $entry->getAffectedObject()->getUserID()}<a href="{link controller='User' id=$entry->getAffectedObject()->getUserID()}{/link}" class="userLink" data-user-id="{@$entry->getAffectedObject()->userID}">{$entry->getAffectedObject()->getUsername()}</a>{else}{$entry->getAffectedObject()->getUsername()}{/if} - {@$entry->getAffectedObject()->getTime()|time} - {lang}wcf.moderation.type.{@$entry->getObjectTypeName()}{/lang}</small>	
						</td>
						<td class="columnText columnAssignedUserID">{if $entry->assignedUserID}<a href="{link controller='User' id=$entry->assignedUserID}{/link}" class="userLink" data-user-id="{@$entry->assignedUserID}">{$entry->assignedUsername}</a>{/if}</td>
						<td class="columnDate columnLastChangeTime">{if $entry->lastChangeTime}{@$entry->lastChangeTime|time}{/if}</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		{hascontent}
			<nav>
				<ul>
					{content}
						{event name='contentNavigationButtonsBottom'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}

</body>
</html>