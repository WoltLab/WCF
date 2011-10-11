{include file='header'}

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/session1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.sessionLog.access.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php/ACPSessionLog/$sessionLogID/?pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
</div>

{hascontent}
	<div class="border boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.sessionLog.access.list{/lang} <span class="badge" title="{lang}wcf.acp.sessionLog.access.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table>
			<thead>
				<tr>
					<th class="columnID columnSessionAccessLogID{if $sortField == 'sessionAccessLogID'} active{/if}"><a href="index.php/ACPSessionLog/{@$sessionLogID}/?pageNo={@$pageNo}&amp;sortField=sessionAccessLogID&amp;sortOrder={if $sortField == 'sessionAccessLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.global.objectID{/lang}{if $sortField == 'sessionAccessLogID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active{/if}"><a href="index.php/ACPSessionLog/{@$sessionLogID}/?pageNo={@$pageNo}&amp;sortField=ipAddress&amp;sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.user.ipAddress{/lang}{if $sortField == 'ipAddress'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate{if $sortField == 'time'} active{/if}"><a href="index.php/ACPSessionLog/{@$sessionLogID}/?pageNo={@$pageNo}&amp;sortField=time&amp;sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.time{/lang}{if $sortField == 'time'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnPackageName{if $sortField == 'packageName'} active{/if}"><a href="index.php/ACPSessionLog/{@$sessionLogID}/?pageNo={@$pageNo}&amp;sortField=packageName&amp;sortOrder={if $sortField == 'packageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.packageName{/lang}{if $sortField == 'packageName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnClassName{if $sortField == 'className'} active{/if}"><a href="index.php/ACPSessionLog/{@$sessionLogID}/?pageNo={@$pageNo}&amp;sortField=className&amp;sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.className{/lang}{if $sortField == 'className'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></p></th>
					<th class="columnURL columnRequestURI{if $sortField == 'requestURI'} active{/if}"><a href="index.php/ACPSessionLog/{@$sessionLogID}/?pageNo={@$pageNo}&amp;sortField=requestURI&amp;sortOrder={if $sortField == 'requestURI' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.requestURI{/lang}{if $sortField == 'requestURI'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnRequestMethod{if $sortField == 'requestMethod'} active{/if}"><a href="index.php/ACPSessionLog/{@$sessionLogID}/?pageNo={@$pageNo}&amp;sortField=requestMethod&amp;sortOrder={if $sortField == 'requestMethod' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.requestMethod{/lang}{if $sortField == 'requestMethod'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=sessionAccessLog}
						<tr>
							<td class="columnID columnSessionAccessLogID"><p>{@$sessionAccessLog->sessionAccessLogID}</p></td>
							<td class="columnURL columnIpAddress"{if $sessionAccessLog->ipAddress != $sessionLog->ipAddress} style="color: red"{/if}><p>{$sessionAccessLog->ipAddress}</p></td>
							<td class="columnDate columnTime"><p>{@$sessionAccessLog->time|time}</p></td>
							<td class="columnTitle columnPackageName"><p>{$sessionAccessLog->packageName}</p></td>
							<td class="columnText columnClassName"><p>{$sessionAccessLog->className}</p></td>
							<td class="columnURL columnRequestURI" title="{$sessionAccessLog->requestURI}"><p>{if !$sessionAccessLog->hasProtectedURI()}<a href="{$sessionAccessLog->requestURI}{@SID_ARG_2ND}">{$sessionAccessLog->requestURI|truncate:50}</a>{else}{$sessionAccessLog->requestURI|truncate:50}{/if}</p></td>
							<td class="columnTextolumnRequestMethod"><p>{$sessionAccessLog->requestMethod}</p></td>
					
							{if $additionalColumns.$sessionAccessLog->sessionAccessLogID|isset}{@$additionalColumns.$sessionAccessLog->sessionAccessLogID}{/if}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
		
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
	</div>
{/hascontent}

{include file='footer'}
