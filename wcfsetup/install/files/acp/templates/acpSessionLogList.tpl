{include file='header' pageTitle='wcf.acp.sessionLog.list'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.sessionLog.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="ACPSessionLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
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
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.sessionLog.list{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.sessionLog.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnSessionLogID{if $sortField == 'sessionLogID'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=sessionLogID&sortOrder={if $sortField == 'sessionLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=username&sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.username{/lang}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=ipAddress&sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.ipAddress{/lang}</a></th>
					<th class="columnText columnUserAgent{if $sortField == 'userAgent'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=userAgent&sortOrder={if $sortField == 'userAgent' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.userAgent{/lang}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.time{/lang}</a></th>
					<th class="columnDate columnLastActivityTime{if $sortField == 'lastActivityTime'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=lastActivityTime&sortOrder={if $sortField == 'lastActivityTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.lastActivityTime{/lang}</a></th>
					<th class="columnDigits columnAccesses{if $sortField == 'accesses'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLogList'}pageNo={@$pageNo}&sortField=accesses&sortOrder={if $sortField == 'accesses' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.actions{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=sessionLog}
					<tr class="{if $sessionLog->active} activeContainer{/if}">
						<td class="columnID columnSessionLogID"><p>{@$sessionLog->sessionLogID}</p></td>
						<td class="columnTitle columnUsername"><p><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->username}</a></p></td>
						<td class="columnSmallText columnIpAddress"><p><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->getIpAddress()}</a>{if $sessionLog->hostname != $sessionLog->ipAddress}<br /><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->hostname}</a>{/if}</p></td>
						<td class="columnSmallText columnUserAgent" title="{$sessionLog->userAgent}"><p><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->userAgent|truncate:75}</a></p></td>
						<td class="columnDate columnTime"><p>{@$sessionLog->time|time}</p></td>
						<td class="columnDate columnLastActivityTime"><p>{@$sessionLog->lastActivityTime|time}</p></td>
						<td class="columnDigits columnAccesses"><p>{#$sessionLog->accesses}</p></td>
						
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
{/if}

{include file='footer'}
