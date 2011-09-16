{include file='header'}

<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/session1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.sessionLog.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=ACPSessionLogList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"|concat:SID_ARG_2ND_NOT_ENCODED}
</div>

{if $sessionLogs|count}
	<div class="border boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.sessionLog.list{/lang} <span class="badge" title="{lang}wcf.acp.sessionLog.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table>
			<thead>
				<tr>
					<th class="columnSessionLogID{if $sortField == 'sessionLogID'} active{/if}"><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=sessionLogID&amp;sortOrder={if $sortField == 'sessionLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.sessionLogID{/lang}{if $sortField == 'sessionLogID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnUsername{if $sortField == 'username'} active{/if}"><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnURL columnIpAddress{if $sortField == 'ipAddress'} active{/if}"><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=ipAddress&amp;sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.ipAddress{/lang}{if $sortField == 'ipAddress'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnUserAgent{if $sortField == 'userAgent'} active{/if}"><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=userAgent&amp;sortOrder={if $sortField == 'userAgent' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.userAgent{/lang}{if $sortField == 'userAgent'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnTime{if $sortField == 'time'} active{/if}"><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=time&amp;sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.time{/lang}{if $sortField == 'time'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnLastActivityTime{if $sortField == 'lastActivityTime'} active{/if}"><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=lastActivityTime&amp;sortOrder={if $sortField == 'lastActivityTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.lastActivityTime{/lang}{if $sortField == 'lastActivityTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnAccesses{if $sortField == 'accesses'} active{/if}"><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=accesses&amp;sortOrder={if $sortField == 'accesses' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.accesses{/lang}{if $sortField == 'accesses'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$sessionLogs item=sessionLog}
				<tr class="{if $sessionLog->active} activeContainer{/if}">
					<td class="columnID columnSessionLogID"><p>{@$sessionLog->sessionLogID}</p></td>
					<td class="columnTitle columnUsername"><p>{if $__wcf->user->userID == $sessionLog->userID}<img src="{@RELATIVE_WCF_DIR}icon/user1.svg" alt="" />{/if} <a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->username}</a></p></td>
					<td class="columnURL columnIpAddress"><p><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->ipAddress}</a><br /><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->hostname}</a></p></td>
					<td class="columnText columnUserAgent"><p><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->userAgent}</a></p></td>
					<td class="columnDate columnTime"><p>{@$sessionLog->time|time}</p></td>
					<td class="columnDate columnLastActivityTime"><p>{@$sessionLog->lastActivityTime|time}</p></td>
					<td class="columnDigits columnAccesses"><p>{#$sessionLog->accesses}</p></td>
					
					{if $additionalColumns.$sessionLog->sessionLogID|isset}{@$additionalColumns.$sessionLog->sessionLogID}{/if}
				</tr>
			{/foreach}
			</tbody>
		</table>
		
	</div>

	<div class="contentFooter">
		{@$pagesLinks}
	</div>
{/if}

{include file='footer'}
