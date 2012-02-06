{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', $('.updateServerRow'));
		new WCF.Action.Toggle('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', $('.updateServerRow'));
	});
	//]]>
</script>

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/server1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.updateServer.list{/lang}</h1>
	</hgroup>
</header>

{if $deletedPackageUpdateServerID}
	<p class="wcf-success">{lang}wcf.acp.updateServer.delete.success{/lang}</p>
{/if}

<div class="wcf-contentHeader">
	{pages print=true assign=pagesLinks controller="UpdateServerList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul class="wcf-largeButtons">
			<li><a href="{link controller='UpdateServerAdd'}{/link}" title="{lang}wcf.acp.updateServer.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li>
			
			{event name='largeButtons'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="wcf-border wcf-boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.updateServer.list{/lang} <span class="wcf-badge" title="{lang}wcf.acp.updateServer.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="wcf-table big">
			<thead>
				<tr>
					<th class="columnID columnPackageUpdateServerID{if $sortField == 'packageUpdateServerID'} active{/if}" colspan="2"><a href="{link controller='UpdateServerList'}pageNo={@$pageNo}&sortField=packageUpdateServerID&sortOrder={if $sortField == 'packageUpdateServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'packageUpdateServerID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnURL columnServer{if $sortField == 'serverURL'} active{/if}"><a href="{link controller='UpdateServerList'}pageNo={@$pageNo}&sortField=serverURL&sortOrder={if $sortField == 'serverURL' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.serverURL{/lang}{if $sortField == 'serverURL'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnPackages{if $sortField == 'packages'} active{/if}"><a href="{link controller='UpdateServerList'}pageNo={@$pageNo}&sortField=packages&sortOrder={if $sortField == 'packages' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.packages{/lang}{if $sortField == 'packages'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnStatus{if $sortField == 'status'} active{/if}"><a href="{link controller='UpdateServerList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.status{/lang}{if $sortField == 'status'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnText columnErrorText{if $sortField == 'errorMessage'} active{/if}"><a href="{link controller='UpdateServerList'}pageNo={@$pageNo}&sortField=errorMessage&sortOrder={if $sortField == 'errorMessage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.errorMessage{/lang}{if $sortField == 'errorMessage'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDate columnTimestamp{if $sortField == 'lastUpdateTime'} active{/if}"><a href="{link controller='UpdateServerList'}pageNo={@$pageNo}&sortField=lastUpdateTime&sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.lastUpdateTime{/lang}{if $sortField == 'lastUpdateTime'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=updateServer}
						<tr class="updateServerRow">
							<td class="columnIcon">
								<img src="{@RELATIVE_WCF_DIR}icon/{if !$updateServer->disabled}enabled{else}disabled{/if}1.svg" alt="" title="{lang}wcf.global.button.{if !$updateServer->disabled}disable{else}enable{/if}{/lang}" class="toggleButton balloonTooltip" data-object-id="{@$updateServer->packageUpdateServerID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" />
								<a href="{link controller='UpdateServerEdit' id=$updateServer->packageUpdateServerID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="balloonTooltip" /></a>
								<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="deleteButton balloonTooltip" data-object-id="{@$updateServer->packageUpdateServerID}" data-confirm-message="{lang}wcf.acp.updateServer.delete.sure{/lang}" />
							
								{event name='buttons'}
							</td>
							<td class="columnID"><p>{@$updateServer->packageUpdateServerID}</p></td>
							<td class="columnText columnTitle"><p><a href="{link controller='UpdateServerEdit' id=$updateServer->packageUpdateServerID}{/link}" title="{lang}wcf.global.button.edit{/lang}">{$updateServer->serverURL}</a></p></td>
							<td class="columnDigits"><p>{#$updateServer->packages}</p></td>
							<td class="columnStatus"><p class="badge{if $updateServer->status == 'online'} badgeSuccess{else} badgeError{/if}">{@$updateServer->status}</p></td>
							<td class="columnText"><p title="{@$updateServer->errorMessage}">{@$updateServer->errorMessage|truncate:"30"}</p></td>
							<td class="columnDate"><p>{if $updateServer->lastUpdateTime}{@$updateServer->lastUpdateTime|time}{/if}</p></td>
						
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
		
	</div>
	
	<div class="wcf-contentFooter">
		{@$pagesLinks}
		
		<nav>
			<ul class="wcf-largeButtons">
				<li><a href="{link controller='UpdateServerAdd'}{/link}" title="{lang}wcf.acp.updateServer.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li>
				
				{event name='largeButtons'}
			</ul>
		</nav>
	</div>
{hascontentelse}
	<div class="wcf-border wcf-content">
		<p class="warning">{lang}wcf.acp.updateServer.list.noneAvailable{/lang}</p>
	</div>
{/hascontent}

{include file='footer'}
