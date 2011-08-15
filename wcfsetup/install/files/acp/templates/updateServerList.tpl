{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', $('.updateServerRow'));
		new WCF.Action.Toggle('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', $('.updateServerRow'));
	});
	//]]>
</script>

<header class="mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/server1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.updateServer.view{/lang}</h1>
	</hgroup>
</header>

{if $deletedPackageUpdateServerID}
	<p class="success">{lang}wcf.acp.updateServer.delete.success{/lang}</p>
{/if}

<div class="contentHeader">
	<nav class="largeButtons">
		<ul><li><a href="index.php?form=UpdateServerAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.updateServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerAddM.png" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li></ul>
	</nav>
</div>

{if !$updateServers|count}
	<div class="border content">
		<p class="warning">{lang}wcf.acp.updateServer.view.noneAvailable{/lang}</p>
	</div>
{else}
	<div class="border boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.updateServer.list.available{/lang} <span class="badge" title="{lang}wcf.acp.updateServer.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table>
			<thead>
				<tr>
					<th class="columnPackageUpdateServerID{if $sortField == 'packageUpdateServerID'} active{/if}" colspan="2"><p><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=packageUpdateServerID&amp;sortOrder={if $sortField == 'packageUpdateServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.packageUpdateServerID{/lang}{if $sortField == 'packageUpdateServerID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnServer{if $sortField == 'serverURL'} active{/if}"><p><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=serverURL&amp;sortOrder={if $sortField == 'serverURL' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.serverURL{/lang}{if $sortField == 'serverURL'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnPackages{if $sortField == 'packages'} active{/if}"><p><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=packages&amp;sortOrder={if $sortField == 'packages' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.packages{/lang}{if $sortField == 'packages'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnStatus{if $sortField == 'status'} active{/if}"><p><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=status&amp;sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.status{/lang}{if $sortField == 'status'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnErrorText{if $sortField == 'errorMessage'} active{/if}"><p><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=errorMessage&amp;sortOrder={if $sortField == 'errorMessage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.errorMessage{/lang}{if $sortField == 'errorMessage'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					<th class="columnTimestamp{if $sortField == 'lastUpdateTime'} active{/if}"><p><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=lastUpdateTime&amp;sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.lastUpdateTime{/lang}{if $sortField == 'lastUpdateTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></p></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$updateServers item=updateServer}
					<tr class="updateServerRow">
						<td class="columnIcon">
							<img src="{@RELATIVE_WCF_DIR}icon/{if !$updateServer->disabled}enabled{else}disabled{/if}1.svg" alt="" title="{lang}wcf.acp.updateServer.{if !$updateServer->disabled}disable{else}enable{/if}{/lang}" data-objectID="{@$updateServer->packageUpdateServerID}" data-disableMessage="{lang}wcf.acp.updateServer.disable{/lang}" data-enableMessage="{lang}wcf.acp.updateServer.enable{/lang}" class="toggleButton balloonTooltip" />
							<a href="index.php?form=UpdateServerEdit&amp;packageUpdateServerID={@$updateServer->packageUpdateServerID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.acp.updateServer.edit{/lang}" class="balloonTooltip" /></a>
							<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.acp.updateServer.delete{/lang}" data-objectID="{@$updateServer->packageUpdateServerID}" data-confirmMessage="{lang}wcf.acp.updateServer.delete.sure{/lang}" class="deleteButton balloonTooltip" />
							
							{if $additionalButtons[$updateServer->packageUpdateServerID]|isset}{@$additionalButtons[$updateServer->packageUpdateServerID]}{/if}
						</td>
						<td class="columnID"><p>{@$updateServer->packageUpdateServerID}</p></td>
						<td class="columnText"><p><a href="index.php?form=UpdateServerEdit&amp;packageUpdateServerID={@$updateServer->packageUpdateServerID}{@SID_ARG_2ND}">{@$updateServer->serverURL}</a></p></td>
						<td class="columnText smallFont"><p>{#$updateServer->packages}</p></td>
						<td class="columnText smallFont"><p class="badge{if $updateServer->status == 'online'} badgeSuccess{else} badgeError{/if}">{@$updateServer->status}</p></td>
						<td class="columnText smallFont"><p title="{@$updateServer->errorMessage}">{@$updateServer->errorMessage|truncate:"30"}</p></td>
						<td class="columnDate smallFont"><p>{if $updateServer->lastUpdateTime}{@$updateServer->lastUpdateTime|time}{/if}</p></td>
						
						{if $additionalColumns[$updateServer->packageUpdateServerID]|isset}{@$additionalColumns[$updateServer->packageUpdateServerID]}{/if}
					</tr>
				{/foreach}
			</tbody>
		</table>
		
	</div>
	
	<div class="contentFooter">
		<nav class="largeButtons">
			<ul><li><a href="index.php?form=UpdateServerAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.updateServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerAddM.png" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li></ul>
		</nav>
	</div>
{/if}

{include file='footer'}
