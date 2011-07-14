{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/sessionLogL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.sessionLog.view{/lang}</h2>
	</div>
</div>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=ACPSessionLogList&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:SID_ARG_2ND_NOT_ENCODED}
</div>

{if $sessionLogs|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.sessionLog.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnSessionLogID{if $sortField == 'sessionLogID'} active{/if}"><div><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=sessionLogID&amp;sortOrder={if $sortField == 'sessionLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.sessionLogID{/lang}{if $sortField == 'sessionLogID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUsername{if $sortField == 'username'} active{/if}"><div><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=username&amp;sortOrder={if $sortField == 'username' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.user.username{/lang}{if $sortField == 'username'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnIpAddress{if $sortField == 'ipAddress'} active{/if}"><div><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=ipAddress&amp;sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.ipAddress{/lang}{if $sortField == 'ipAddress'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnUserAgent{if $sortField == 'userAgent'} active{/if}"><div><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=userAgent&amp;sortOrder={if $sortField == 'userAgent' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.userAgent{/lang}{if $sortField == 'userAgent'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTime{if $sortField == 'time'} active{/if}"><div><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=time&amp;sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.time{/lang}{if $sortField == 'time'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnLastActivityTime{if $sortField == 'lastActivityTime'} active{/if}"><div><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=lastActivityTime&amp;sortOrder={if $sortField == 'lastActivityTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.lastActivityTime{/lang}{if $sortField == 'lastActivityTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnAccesses{if $sortField == 'accesses'} active{/if}"><div><a href="index.php?page=ACPSessionLogList&amp;pageNo={@$pageNo}&amp;sortField=accesses&amp;sortOrder={if $sortField == 'accesses' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.accesses{/lang}{if $sortField == 'accesses'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$sessionLogs item=sessionLog}
				<tr class="{cycle values="container-1,container-2"}{if $sessionLog->active} activeContainer{/if}">
					<td class="columnSessionLogID columnID">{@$sessionLog->sessionLogID}</td>
					<td class="columnUsername columnText">{if $__wcf->user->userID == $sessionLog->userID}<img src="{@RELATIVE_WCF_DIR}icon/userS.png" alt="" />{/if} <a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->username}</a></td>
					<td class="columnIpAddress columnText"><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->ipAddress}</a><br /><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->hostname}</a></td>
					<td class="columnUserAgent columnText smallFont"><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLog->sessionLogID}{@SID_ARG_2ND}">{$sessionLog->userAgent}</a></td>
					<td class="columnTime columnText">{@$sessionLog->time|time}</td>
					<td class="columnLastActivityTime columnText">{@$sessionLog->lastActivityTime|time}</td>
					<td class="columnAccesses columnNumbers">{#$sessionLog->accesses}</td>
					
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
