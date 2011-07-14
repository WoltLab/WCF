{include file='header'}
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/MultiPagesLinks.class.js"></script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/sessionLogL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.sessionLog.access.view{/lang}</h2>
	</div>
</div>

<div class="contentHeader">
	{pages print=true assign=pagesLinks link="index.php?page=ACPSessionLog&sessionLogID=$sessionLogID&pageNo=%d&sortField=$sortField&sortOrder=$sortOrder&packageID="|concat:SID_ARG_2ND_NOT_ENCODED}
</div>

{if $sessionAccessLogs|count}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.sessionLog.access.view.count{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnSessionAccessLogID{if $sortField == 'sessionAccessLogID'} active{/if}"><div><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLogID}&amp;pageNo={@$pageNo}&amp;sortField=sessionAccessLogID&amp;sortOrder={if $sortField == 'sessionAccessLogID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.sessionAccessLogID{/lang}{if $sortField == 'sessionAccessLogID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnIpAddress{if $sortField == 'ipAddress'} active{/if}"><div><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLogID}&amp;pageNo={@$pageNo}&amp;sortField=ipAddress&amp;sortOrder={if $sortField == 'ipAddress' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.ipAddress{/lang}{if $sortField == 'ipAddress'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTime{if $sortField == 'time'} active{/if}"><div><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLogID}&amp;pageNo={@$pageNo}&amp;sortField=time&amp;sortOrder={if $sortField == 'time' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.time{/lang}{if $sortField == 'time'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnPackageName{if $sortField == 'packageName'} active{/if}"><div><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLogID}&amp;pageNo={@$pageNo}&amp;sortField=packageName&amp;sortOrder={if $sortField == 'packageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.packageName{/lang}{if $sortField == 'packageName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnClassName{if $sortField == 'className'} active{/if}"><div><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLogID}&amp;pageNo={@$pageNo}&amp;sortField=className&amp;sortOrder={if $sortField == 'className' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.className{/lang}{if $sortField == 'className'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnRequestURI{if $sortField == 'requestURI'} active{/if}"><div><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLogID}&amp;pageNo={@$pageNo}&amp;sortField=requestURI&amp;sortOrder={if $sortField == 'requestURI' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.requestURI{/lang}{if $sortField == 'requestURI'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnRequestMethod{if $sortField == 'requestMethod'} active{/if}"><div><a href="index.php?page=ACPSessionLog&amp;sessionLogID={@$sessionLogID}&amp;pageNo={@$pageNo}&amp;sortField=requestMethod&amp;sortOrder={if $sortField == 'requestMethod' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.sessionLog.requestMethod{/lang}{if $sortField == 'requestMethod'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalColumnHeads|isset}{@$additionalColumnHeads}{/if}
				</tr>
			</thead>
			<tbody>
			{foreach from=$sessionAccessLogs item=sessionAccessLog}
				<tr class="{cycle values="container-1,container-2"} smallFont">
					<td class="columnSessionAccessLogID columnID">{@$sessionAccessLog->sessionAccessLogID}</td>
					<td class="columnIpAddress columnText"{if $sessionAccessLog->ipAddress != $sessionLog->ipAddress} style="color: red"{/if}>{$sessionAccessLog->ipAddress}</td>
					<td class="columnTime columnText">{@$sessionAccessLog->time|time}</td>
					<td class="columnPackageName columnText">{$sessionAccessLog->packageName}</td>
					<td class="columnClassName columnText">{$sessionAccessLog->className}</td>
					<td class="columnRequestURI columnText" title="{$sessionAccessLog->requestURI}">{if !$sessionAccessLog->hasProtectedURI()}<a href="{$sessionAccessLog->requestURI}{@SID_ARG_2ND}">{$sessionAccessLog->requestURI|truncate:50}</a>{else}{$sessionAccessLog->requestURI|truncate:50}{/if}</td>
					<td class="columnRequestMethod columnText">{$sessionAccessLog->requestMethod}</td>
					
					{if $additionalColumns.$sessionAccessLog->sessionAccessLogID|isset}{@$additionalColumns.$sessionAccessLog->sessionAccessLogID}{/if}
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
