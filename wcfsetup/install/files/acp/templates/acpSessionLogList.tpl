{include file='header'}

<header class="box48 boxHeadline">
	<img src="{@$__wcf->getPath()}icon/session1.svg" alt="" class="icon48" />
	<hgroup>
		<h1>{lang}wcf.acp.sessionLog.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="ACPSessionLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop shadow">
		<hgroup>
			<h1>{lang}wcf.acp.sessionLog.list{/lang} <span class="badge" title="{lang}wcf.acp.sessionLog.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnSessionLogID{if $sortField == 'sessionLogID'} active{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=sessionLogID&sortOrder={if $sortField == 'sessionLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'sessionLogID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=ipAddress&sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.ipAddress{/lang}{if $sortField == 'ipAddress'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnUserAgent{if $sortField == 'userAgent'} active{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=userAgent&sortOrder={if $sortField == 'userAgent' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.userAgent{/lang}{if $sortField == 'userAgent'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.time{/lang}{if $sortField == 'time'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnLastActivityTime{if $sortField == 'lastActivityTime'} active{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=lastActivityTime&sortOrder={if $sortField == 'lastActivityTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.lastActivityTime{/lang}{if $sortField == 'lastActivityTime'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnAccesses{if $sortField == 'accesses'} active{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=accesses&sortOrder={if $sortField == 'accesses' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.accesses{/lang}{if $sortField == 'accesses'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=sessionLog}
						<tr class="{if $sessionLog->active} activeContainer{/if}">
							<td class="columnID columnSessionLogID"><p>{@$sessionLog->sessionLogID}</p></td>
							<td class="columnTitle columnUsername"><p>{if $__wcf->user->userID == $sessionLog->userID}<img src="{@$__wcf->getPath()}icon/user1.svg" alt="" class="icon16" />{/if} <a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->username}</a></p></td>
							<td class="columnURL columnIpAddress"><p><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->ipAddress}</a>{if $sessionLog->hostname != $sessionLog->ipAddress}<br /><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->hostname}</a>{/if}</p></td>
							<td class="columnText columnUserAgent"><p><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->userAgent}</a></p></td>
							<td class="columnDate columnTime"><p>{@$sessionLog->time|time}</p></td>
							<td class="columnDate columnLastActivityTime"><p>{@$sessionLog->lastActivityTime|time}</p></td>
							<td class="columnDigits columnAccesses"><p>{#$sessionLog->accesses}</p></td>
					
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
		
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
	</div>
{/hascontent}

{include file='footer'}
