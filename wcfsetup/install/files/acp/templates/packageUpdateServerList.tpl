{include file='header' pageTitle='wcf.acp.updateServer.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', '.jsUpdateServerRow');
		new WCF.Action.Toggle('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', '.jsUpdateServerRow');
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.updateServer.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="PackageUpdateServerList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='PackageUpdateServerAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<div class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.updateServer.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnPackageUpdateServerID{if $sortField == 'packageUpdateServerID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=packageUpdateServerID&sortOrder={if $sortField == 'packageUpdateServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnURL columnServer{if $sortField == 'serverURL'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=serverURL&sortOrder={if $sortField == 'serverURL' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.serverURL{/lang}</a></th>
					<th class="columnDigits columnPackages{if $sortField == 'packages'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=packages&sortOrder={if $sortField == 'packages' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.packages{/lang}</a></th>
					<th class="columnStatus{if $sortField == 'status'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.status{/lang}</a></th>
					<th class="columnText columnErrorText{if $sortField == 'errorMessage'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=errorMessage&sortOrder={if $sortField == 'errorMessage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.errorMessage{/lang}</a></th>
					<th class="columnDate columnTimestamp{if $sortField == 'lastUpdateTime'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=lastUpdateTime&sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.lastUpdateTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=updateServer}
						<tr class="jsUpdateServerRow">
							<td class="columnIcon">
								<span class="icon icon16 icon-check{if $updateServer->isDisabled}-empty{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$updateServer->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$updateServer->packageUpdateServerID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}"></span>
								<a href="{link controller='PackageUpdateServerEdit' id=$updateServer->packageUpdateServerID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
								<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$updateServer->packageUpdateServerID}" data-confirm-message="{lang}wcf.acp.updateServer.delete.sure{/lang}"></span>
								
								{event name='itemButtons'}
							</td>
							<td class="columnID">{@$updateServer->packageUpdateServerID}</td>
							<td class="columnText columnTitle"><a href="{link controller='PackageUpdateServerEdit' id=$updateServer->packageUpdateServerID}{/link}" title="{lang}wcf.acp.updateServer.edit{/lang}">{$updateServer->serverURL}</a></td>
							<td class="columnDigits">{#$updateServer->packages}</td>
							<td class="columnStatus"><span class="badge{if $updateServer->status == 'online'} green{else} red{/if}">{@$updateServer->status}</span></td>
							<td class="columnText" title="{@$updateServer->errorMessage}">{@$updateServer->errorMessage|truncate:"30"}</td>
							<td class="columnDate">{if $updateServer->lastUpdateTime}{@$updateServer->lastUpdateTime|time}{/if}</td>
							
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
				<li><a href="{link controller='PackageUpdateServerAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{hascontentelse}
	<p class="warning">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

{include file='footer'}
