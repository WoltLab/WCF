{include file='header' pageTitle='wcf.acp.updateServer.list'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', '.jsUpdateServerRow');
		new WCF.Action.Toggle('wcf\\data\\package\\update\\server\\PackageUpdateServerAction', '.jsUpdateServerRow');
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.updateServer.list{/lang}{if $items} <span class="badge badgeInverse">{#$items}</span>{/if}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='PackageUpdateServerAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller="PackageUpdateServerList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnPackageUpdateServerID{if $sortField == 'packageUpdateServerID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=packageUpdateServerID&sortOrder={if $sortField == 'packageUpdateServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnServerURL{if $sortField == 'serverURL'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=serverURL&sortOrder={if $sortField == 'serverURL' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.serverURL{/lang}</a></th>
					<th class="columnLoginUsername{if $sortField == 'loginUsername'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=loginUsername&sortOrder={if $sortField == 'loginUsername' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.loginUsername{/lang}</a></th>
					<th class="columnDigits columnPackages{if $sortField == 'packages'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=packages&sortOrder={if $sortField == 'packages' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.packages{/lang}</a></th>
					<th class="columnStatus{if $sortField == 'status'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=status&sortOrder={if $sortField == 'status' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.status{/lang}</a></th>
					<th class="columnText columnErrorText{if $sortField == 'errorMessage'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=errorMessage&sortOrder={if $sortField == 'errorMessage' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.errorMessage{/lang}</a></th>
					<th class="columnDate columnTimestamp{if $sortField == 'lastUpdateTime'} active {@$sortOrder}{/if}"><a href="{link controller='PackageUpdateServerList'}pageNo={@$pageNo}&sortField=lastUpdateTime&sortOrder={if $sortField == 'lastUpdateTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.updateServer.lastUpdateTime{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=updateServer}
					<tr class="jsUpdateServerRow">
						<td class="columnIcon">
							{if $updateServer->canDisable()}
								<span class="icon icon16 fa-{if !$updateServer->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$updateServer->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$updateServer->packageUpdateServerID}"></span>
							{else}
								<span class="icon icon16 fa-check-square-o disabled"></span>
							{/if}
							<a href="{link controller='PackageUpdateServerEdit' id=$updateServer->packageUpdateServerID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							<span class="icon icon16 fa-times {if $updateServer->canDelete()}jsDeleteButton jsTooltip pointer{else}disabled{/if}"{if $updateServer->canDelete()} title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$updateServer->packageUpdateServerID}" data-confirm-message-html="{lang __encode=true}wcf.acp.updateServer.delete.sure{/lang}"{/if}></span>
							
							{event name='itemButtons'}
						</td>
						<td class="columnID columnPackageUpdateServerID">{@$updateServer->packageUpdateServerID}</td>
						<td class="columnTitle columnServerURL"><a href="{link controller='PackageUpdateServerEdit' id=$updateServer->packageUpdateServerID}{/link}" title="{lang}wcf.acp.updateServer.edit{/lang}">{$updateServer->serverURL}</a></td>
						<td class="columnLoginUsername">{$updateServer->loginUsername}</td>
						<td class="columnDigits columnPackages">{#$updateServer->packages}</td>
						<td class="columnStatus"><span class="badge{if $updateServer->status == 'online'} green{else} red{/if}">{@$updateServer->status}</span></td>
						<td class="columnText columnErrorText" title="{@$updateServer->errorMessage}">{@$updateServer->errorMessage|truncate:"30"}</td>
						<td class="columnDate columnTimestamp">{if $updateServer->lastUpdateTime}{@$updateServer->lastUpdateTime|time}{/if}</td>
						
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
				<li><a href="{link controller='PackageUpdateServerAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.updateServer.add{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
