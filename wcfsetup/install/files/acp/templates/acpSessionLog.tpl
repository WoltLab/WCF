{include file='header' pageTitle='wcf.acp.sessionLog.access.list'}

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.sessionLog.access.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='ACPSessionLog' id=$sessionLogID link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='ACPSessionLogList'}{/link}" title="{lang}wcf.acp.sessionLog.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.sessionLog.list{/lang}</span></a></li>
			
			{event name='largeButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.sessionLog.access.list{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.sessionLog.access.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnSessionAccessLogID{if $sortField == 'sessionAccessLogID'} active{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=sessionAccessLogID&sortOrder={if $sortField == 'sessionAccessLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'sessionAccessLogID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=ipAddress&sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.user.ipAddress{/lang}{if $sortField == 'ipAddress'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate{if $sortField == 'time'} active{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=time&sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.time{/lang}{if $sortField == 'time'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnClassName{if $sortField == 'className'} active{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=className&sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.className{/lang}{if $sortField == 'className'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnURL columnRequestURI{if $sortField == 'requestURI'} active{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=requestURI&sortOrder={if $sortField == 'requestURI' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.requestURI{/lang}{if $sortField == 'requestURI'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnRequestMethod{if $sortField == 'requestMethod'} active{/if}"><a href="{link controller='ACPSessionLog' id=$sessionLogID}pageNo={@$pageNo}&sortField=requestMethod&sortOrder={if $sortField == 'requestMethod' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.sessionLog.requestMethod{/lang}{if $sortField == 'requestMethod'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=sessionAccessLog}
						<tr>
							<td class="columnID columnSessionAccessLogID"><p>{@$sessionAccessLog->sessionAccessLogID}</p></td>
							<td class="columnURL columnIpAddress{if $sessionAccessLog->ipAddress != $sessionLog->ipAddress} hot{/if}"><p>{$sessionAccessLog->ipAddress}</p></td>
							<td class="columnDate columnTime"><p>{@$sessionAccessLog->time|time}</p></td>
							<td class="columnText columnClassName"><p>{$sessionAccessLog->className}</p></td>
							<td class="columnURL columnRequestURI" title="{$sessionAccessLog->requestURI}"><p>{if !$sessionAccessLog->hasProtectedURI()}<a href="{$sessionAccessLog->requestURI}{@SID_ARG_2ND}">{$sessionAccessLog->requestURI|truncate:50}</a>{else}{$sessionAccessLog->requestURI|truncate:50}{/if}</p></td>
							<td class="columnTextolumnRequestMethod"><p>{$sessionAccessLog->requestMethod}</p></td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='ACPSessionLogList'}{/link}" title="{lang}wcf.acp.sessionLog.list{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/list.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.sessionLog.list{/lang}</span></a></li>
				
				{event name='largeButtonsBottom'}
			</ul>
		</nav>
	</div>
{/hascontent}

{include file='footer'}
