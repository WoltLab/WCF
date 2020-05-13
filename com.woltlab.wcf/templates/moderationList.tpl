{capture assign='pageTitle'}{if $status == 2}{lang}wcf.moderation.doneItems{/lang}{else}{lang}wcf.moderation.outstandingItems{/lang}{/if}{if $pageNo > 1} - {lang}wcf.page.pageNo{/lang}{/if}{/capture}

{capture assign='contentTitle'}{if $status == 2}{lang}wcf.moderation.doneItems{/lang}{else}{lang}wcf.moderation.outstandingItems{/lang}{/if} <span class="badge">{#$items}</span>{/capture}

{capture assign='sidebarRight'}
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
	<div class="section tabularBox messageGroupList moderationList moderationQueueEntryList">
		<ol class="tabularList">
			<li class="tabularListRow tabularListRowHead">
				<ol class="tabularListColumns">
					<li class="columnSort">
						<ul class="inlineList">
							<li>
								<a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField={$sortField}&sortOrder={if $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">
									<span class="icon icon16 fa-sort-amount-{$sortOrder|strtolower} jsTooltip" title="{lang}wcf.search.sortBy{/lang} ({lang}wcf.global.sortOrder.{if $sortOrder === 'ASC'}ascending{else}descending{/if}{/lang})"></span>
								</a>
							</li>
							<li>
								<div class="dropdown">
									<span class="dropdownToggle">{lang}wcf.moderation.{$sortField}{/lang}</span>
		
									<ul class="dropdownMenu">
										{foreach from=$validSortFields item=_sortField}
											<li{if $_sortField === $sortField} class="active"{/if}><a href="{link controller='ModerationList'}definitionID={@$definitionID}&assignedUserID={@$assignedUserID}&status={@$status}&pageNo={@$pageNo}&sortField={$_sortField}&sortOrder={if $sortField == $_sortField && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.moderation.{$_sortField}{/lang}</a></li>
										{/foreach}
									</ul>
								</div>
							</li>
						</ul>
					</li>
					{hascontent}
						<li class="columnFilter">
							<ul class="inlineList">
								{content}
									{if $definitionID}
										<li>
											<span class="icon icon16 fa-tag jsTooltip" title="{lang}wcf.moderation.filterByType{/lang}"></span>
											{lang}wcf.moderation.type.{$availableDefinitions[$definitionID]}{/lang}
										</li>
									{/if}
									
									{if !$assignedUserID || $assignedUserID == $__wcf->getUser()->userID}
										<li>
											<span class="icon icon16 fa-user jsTooltip" title="{lang}wcf.moderation.filterByUser{/lang}"></span>
											{if !$assignedUserID}
												{lang}wcf.moderation.filterByUser.nobody{/lang}
											{else}
												{lang}wcf.moderation.filterByUser.myself{/lang}
											{/if}
										</li>
									{/if}
									
									{if $status == -1 || $status == 2}
										<li>
											{if $status == -1}
												<span class="icon icon16 fa-circle-o jsTooltip" title="{lang}wcf.moderation.status{/lang}"></span>
												{lang}wcf.moderation.status.outstanding{/lang}
											{else}
												<span class="icon icon16 fa-check-circle-o jsTooltip" title="{lang}wcf.moderation.status{/lang}"></span>
												{lang}wcf.moderation.status.done{/lang}
											{/if}
										</li>
									{/if}
								{/content}
							</ul>
						</li>
					{/hascontent}
					<li class="columnApplyFilter jsOnly">
						<button class="small jsStaticDialog" data-dialog-id="moderationListSortFilter"><span class="icon icon16 fa-filter"></span> {lang}wcf.global.filter{/lang}</button>
					</li>
				</ol>
			</li>
			
			{foreach from=$objects item=entry}
				<li class="tabularListRow">
					<ol class="tabularListColumns messageGroup moderationQueueEntry{if $entry->isNew()} new{/if}" data-queue-id="{@$entry->queueID}">
						<li class="columnIcon columnAvatar">
							<div>
								<p{if $entry->isNew()} title="{lang}wcf.moderation.markAsRead.doubleClick{/lang}"{/if}>{@$entry->getUserProfile()->getAvatar()->getImageTag(48)}</p>
								
								{if $entry->assignedUserID}
									<small class="myAvatar jsTooltip" title="{lang}wcf.moderation.assignedUser{/lang}">{@$entry->getAssignedUserProfile()->getAvatar()->getImageTag(24)}</small>
								{/if}
							</div>
						</li>
						<li class="columnSubject">
							<ul class="labelList">
								<li><span class="badge label">{$entry->getLabel()}</span></li>
							</ul>
							
							<h3>
								<a href="{$entry->getLink()}" class="messageGroupLink">{$entry->getTitle()}</a>
								{if $entry->comments}
									<span class="badge messageGroupCounterMobile">{@$entry->comments|shortUnit}</span>
								{/if}
							</h3>
							
							<ul class="inlineList dotSeparated small messageGroupInfo">
								<li class="messageGroupAuthor">{if $entry->getAffectedObject()->getUserID()}<a href="{link controller='User' id=$entry->getAffectedObject()->getUserID() title=$entry->getAffectedObject()->getUsername()}{/link}" class="userLink" data-user-id="{@$entry->getAffectedObject()->getUserID()}">{$entry->getAffectedObject()->getUsername()}</a>{else}{$entry->getAffectedObject()->getUsername()}{/if}</li>
								<li class="messageGroupTime">{@$entry->getAffectedObject()->getTime()|time}</li>
								<li>{lang}wcf.moderation.type.{@$entry->getObjectTypeName()}{/lang}</li>
								
								{event name='messageGroupInfo'}
							</ul>
							
							<ul class="messageGroupInfoMobile">
								<li class="messageGroupAuthorMobile">{$entry->getAffectedObject()->getUsername()}</li>
								<li class="messageGroupLastPostTimeMobile">{if $entry->lastChangeTime}{@$entry->lastChangeTime|time}{/if}</li>
							</ul>
							
							{if $entry->assignedUserID}
								<small class="moderationQueueEntryAssignedUser">
									{lang}wcf.moderation.assignedUser{/lang}: <a href="{link controller='User' id=$entry->assignedUserID}{/link}" class="userLink" data-user-id="{@$entry->assignedUserID}">{$entry->assignedUsername}</a>
								</small>
							{/if}
							
							{event name='moderationQueueEntryData'}
						</li>
						<li class="columnStats">{@$entry->comments|shortUnit}</li>
						<li class="columnLastPost">
							{if $entry->lastChangeTime}{@$entry->lastChangeTime|time}{/if}
						</li>
						
						{event name='columns'}
					</ol>
				</li>
			{/foreach}
		</ol>
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
	<p class="info" role="status">{lang}wcf.moderation.noEntries{/lang}</p>
{/if}

<div id="moderationListSortFilter" class="jsStaticDialogContent" data-title="{lang}wcf.moderation.filter{/lang}">
	<form method="post" action="{link controller='ModerationList'}{/link}">
		<div class="section">
			<dl>
				<dt><label for="definitionID">{lang}wcf.moderation.filterByType{/lang}</label></dt>
				<dd>
					<select name="definitionID" id="definitionID">
						<option value="0">{lang}wcf.moderation.type.all{/lang}</option>
						{foreach from=$availableDefinitions key=__definitionID item=definitionName}
							<option value="{$__definitionID}"{if $__definitionID == $definitionID} selected{/if}>{lang}wcf.moderation.type.{$definitionName}{/lang}</option>
						{/foreach}

						{event name='filterModerationType'}
					</select>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="assignedUserID">{lang}wcf.moderation.filterByUser{/lang}</label></dt>
				<dd>
					<select name="assignedUserID" id="assignedUserID">
						<option value="-1"{if $assignedUserID == -1} selected{/if}>{lang}wcf.moderation.filterByUser.allEntries{/lang}</option>
						<option value="0"{if $assignedUserID == 0} selected{/if}>{lang}wcf.moderation.filterByUser.nobody{/lang}</option>
						<option value="{@$__wcf->getUser()->userID}"{if $assignedUserID == $__wcf->getUser()->userID} selected{/if}>{lang}wcf.moderation.filterByUser.myself{/lang}</option>
						
						{event name='filterAssignedUser'}
					</select>
				</dd>
			</dl>

			<dl>
				<dt><label for="status">{lang}wcf.moderation.status{/lang}</label></dt>
				<dd>
					<select name="status" id="status">
						<option value="-1"{if $status == -1} selected{/if}>{lang}wcf.moderation.status.outstanding{/lang}</option>
						<option value="2"{if $status == 2} selected{/if}>{lang}wcf.moderation.status.done{/lang}</option>
						
						{event name='filterStatus'}
					</select>
				</dd>
			</dl>
		</div>

		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			<a href="{link controller='ModerationList'}{/link}" class="button">{lang}wcf.global.button.reset{/lang}</a>
			<input type="hidden" name="sortField" value="{$sortField}">
			<input type="hidden" name="sortOrder" value="{$sortOrder}">
		</div>
	</form>
</div>

<script data-relocate="true">
	$(function() {
		new WCF.Moderation.Queue.MarkAsRead();
		new WCF.Moderation.Queue.MarkAllAsRead();
	});
</script>

{include file='footer'}
