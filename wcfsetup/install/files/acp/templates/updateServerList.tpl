{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', $('.updateServerRow'));
		new WCF.Action.Toggle('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', $('.updateServerRow'));
	});
	//]]>
</script>

<div class="mainHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/updateServerL.png" alt="" />
	<div class="headlineContainer">
		<h2>{lang}wcf.acp.updateServer.view{/lang}</h2>
	</div>
</div>

{if $deletedPackageUpdateServerID}
	<p class="success">{lang}wcf.acp.updateServer.delete.success{/lang}</p>
{/if}

<div class="contentHeader">
	<div class="largeButtons">
		<ul><li><a href="index.php?form=UpdateServerAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.updateServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerAddM.png" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li></ul>
	</div>
</div>

{if !$updateServers|count}
	<div class="border content">
		<div class="container-1">
			<p>{lang}wcf.acp.updateServer.view.noneAvailable{/lang}</p>
		</div>
	</div>
{else}
	<div class="border titleBarPanel">
		<div class="containerHead"><h3>{lang}wcf.acp.updateServer.list.available{/lang}</h3></div>
	</div>
	<div class="border borderMarginRemove">
		<table class="tableList">
			<thead>
				<tr class="tableHead">
					<th class="columnPackageUpdateServerID{if $sortField == 'packageUpdateServerID'} active{/if}" colspan="2"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=packageUpdateServerID&amp;sortOrder={if $sortField == 'packageUpdateServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.packageUpdateServerID{/lang}{if $sortField == 'packageUpdateServerID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnServer{if $sortField == 'server'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=server&amp;sortOrder={if $sortField == 'server' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.server{/lang}{if $sortField == 'server'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnPackages{if $sortField == 'packages'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=packages&amp;sortOrder={if $sortField == 'packages' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.packages{/lang}{if $sortField == 'packages'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnStatus{if $sortField == 'status'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=status&amp;sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.status{/lang}{if $sortField == 'status'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnErrorText{if $sortField == 'errorMessage'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=errorMessage&amp;sortOrder={if $sortField == 'errorMessage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.errorMessage{/lang}{if $sortField == 'errorMessage'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					<th class="columnTimestamp{if $sortField == 'lastUpdateTime'} active{/if}"><div><a href="index.php?page=UpdateServerList&amp;pageNo={@$pageNo}&amp;sortField=lastUpdateTime&amp;sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@SID_ARG_2ND}">{lang}wcf.acp.updateServer.lastUpdateTime{/lang}{if $sortField == 'lastUpdateTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}S.png" alt="" />{/if}</a></div></th>
					
					{if $additionalHeadColumns|isset}{@$additionalHeadColumns}{/if}
				</tr>
			</thead>
			<tbody>
				{foreach from=$updateServers item=updateServer}
					<tr class="updateServerRow {cycle values="container-1,container-2"}">
						<td class="columnIcon">
							<img src="{@RELATIVE_WCF_DIR}icon/{if !$updateServer->disabled}enabled{else}disabled{/if}S.png" alt="" class="toggleButton" title="{lang}wcf.acp.updateServer.{if !$updateServer->disabled}disable{else}enable{/if}{/lang}" data-objectID="{@$updateServer->packageUpdateServerID}" data-disableMessage="{lang}wcf.acp.updateServer.disable{/lang}" data-enableMessage="{lang}wcf.acp.updateServer.enable{/lang}" />
							<a href="index.php?form=UpdateServerEdit&amp;packageUpdateServerID={@$updateServer->packageUpdateServerID}{@SID_ARG_2ND}"><img src="{@RELATIVE_WCF_DIR}icon/editS.png" alt="" title="{lang}wcf.acp.updateServer.edit{/lang}" /></a>
							<img src="{@RELATIVE_WCF_DIR}icon/deleteS.png" alt="" title="{lang}wcf.acp.updateServer.delete{/lang}" class="deleteButton" data-objectID="{@$updateServer->packageUpdateServerID}" data-confirmMessage="{lang}wcf.acp.updateServer.delete.sure{/lang}" />
							
							{if $additionalButtons[$updateServer->packageUpdateServerID]|isset}{@$additionalButtons[$updateServer->packageUpdateServerID]}{/if}
						</td>
						<td class="columnID">{@$updateServer->packageUpdateServerID}</td>
						<td class="columnText">
							<a href="index.php?form=UpdateServerEdit&amp;packageUpdateServerID={@$updateServer->packageUpdateServerID}{@SID_ARG_2ND}">
								{@$updateServer->serverURL}
							</a>
						</td>
						<td class="columnText smallFont">
							{#$updateServer->packages}
						</td>
						<td class="columnText smallFont" style="color: {if $updateServer->status == 'online'}green{else}red{/if}">
							{@$updateServer->status}
						</td>
						<td class="columnText smallFont">
							<div title="{@$updateServer->errorMessage}">
								{@$updateServer->errorMessage|truncate:"30"}
							</div>
						</td>
						<td class="columnDate smallFont">
							{if $updateServer->lastUpdateTime}{@$updateServer->lastUpdateTime|time}{/if}
						</td>
						
						{if $additionalColumns[$updateServer->packageUpdateServerID]|isset}{@$additionalColumns[$updateServer->packageUpdateServerID]}{/if}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class="contentHeader">
		<div class="largeButtons">
			<ul><li><a href="index.php?form=UpdateServerAdd{@SID_ARG_2ND}" title="{lang}wcf.acp.updateServer.add{/lang}"><img src="{@RELATIVE_WCF_DIR}icon/updateServerAddM.png" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li></ul>
		</div>
	</div>
{/if}

{include file='footer'}
