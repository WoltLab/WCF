{include file='header' pageTitle='wcf.acp.sessionLog.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.sessionLog.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="ACPSessionLogList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
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
					<tr>
						<td class="columnID columnSessionLogID">{@$sessionLog->sessionLogID}</td>
						<td class="columnTitle columnUsername"><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->username}</a></td>
						<td class="columnSmallText columnIpAddress"><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->getIpAddress()}</a>{if $sessionLog->hostname != $sessionLog->ipAddress}<br><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->hostname}</a>{/if}</td>
						<td class="columnSmallText columnUserAgent" title="{$sessionLog->userAgent}"><a href="{link controller='ACPSessionLog' id=$sessionLog->sessionLogID}{/link}">{$sessionLog->userAgent|truncate:75|tableWordwrap}</a></td>
						<td class="columnDate columnTime">{@$sessionLog->time|time}</td>
						<td class="columnDate columnLastActivityTime">{@$sessionLog->lastActivityTime|time}</td>
						<td class="columnDigits columnAccesses">{#$sessionLog->accesses}</td>
						
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
{/if}

{include file='footer'}
