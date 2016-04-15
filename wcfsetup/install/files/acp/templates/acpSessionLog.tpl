{include file='header' pageTitle='wcf.acp.sessionLog.access.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.sessionLog.access.list{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='ACPSessionLogList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.sessionLog.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='ACPSessionLog' id=$sessionLogID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnSessionAccessLogID{if $sortField == 'sessionAccessLogID'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=sessionAccessLogID&sortOrder={if $sortField == 'sessionAccessLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=ipAddress&sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.ipAddress{/lang}</a></th>
					<th class="columnDate{if $sortField == 'time'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.time{/lang}</a></th>
					<th class="columnText columnClassName{if $sortField == 'className'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=className&sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.className{/lang}</a></th>
					<th class="columnURL columnRequestURI{if $sortField == 'requestURI'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=requestURI&sortOrder={if $sortField == 'requestURI' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.requestURI{/lang}</a></th>
					<th class="columnText columnRequestMethod{if $sortField == 'requestMethod'} active {@$sortOrder}{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=requestMethod&sortOrder={if $sortField == 'requestMethod' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.requestMethod{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=sessionAccessLog}
					<tr>
						<td class="columnID columnSessionAccessLogID">{@$sessionAccessLog->sessionAccessLogID}</td>
						<td class="columnURL columnIpAddress{if $sessionAccessLog->ipAddress != $sessionLog->ipAddress} hot{/if}">{$sessionAccessLog->getIpAddress()}</td>
						<td class="columnDate columnTime">{@$sessionAccessLog->time|time}</td>
						<td class="columnText columnClassName">{$sessionAccessLog->className}</td>
						<td class="columnURL columnRequestURI" title="{$sessionAccessLog->requestURI}">{if !$sessionAccessLog->hasProtectedURI()}<a href="{$sessionAccessLog->requestURI}{@SID_ARG_2ND}">{$sessionAccessLog->requestURI|truncate:50|tableWordwrap}</a>{else}{$sessionAccessLog->requestURI|truncate:50|tableWordwrap}{/if}</td>
						<td class="columnText columnRequestMethod">{$sessionAccessLog->requestMethod}</td>
						
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
		
		<nav class="contentFooterNavigation">
			<ul>
				<li><a href="{link controller='ACPSessionLogList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.sessionLog.list{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{/if}

{include file='footer'}
