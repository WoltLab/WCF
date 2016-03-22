{include file='header' pageTitle='wcf.acp.languageServer.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\language\\server\\LanguageServerAction', '.jsLanguageServerRow');
		new WCF.Action.Toggle('wcf\\data\\language\\server\\LanguageServerAction', '.jsLanguageServerRow');
	});
	//]]>
</script>

<header class="contentHeader">
	<h1 class="contentTitle">{lang}wcf.acp.languageServer.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='LanguageServerList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	<nav>
		<ul>
			<li><a href="{link controller='LanguageServerAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.languageServer.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{if !$languageServers|count}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{else}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLanguageServerID{if $sortField == 'languageServerID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='LanguageServerList'}pageNo={@$pageNo}&sortField=languageServerID&sortOrder={if $sortField == 'languageServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnServerURL{if $sortField == 'serverURL'} active {@$sortOrder}{/if}"><a href="{link controller='LanguageServerList'}pageNo={@$pageNo}&sortField=serverURL&sortOrder={if $sortField == 'serverURL' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.languageServer.serverURL{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			<tbody>
				{foreach from=$languageServers item=languageServer}
					<tr class="jsLanguageServerRow">
						<td class="columnIcon">
							<span class="icon icon16 fa-{if !$languageServer->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if !$languageServer->isDisabled}disable{else}enable{/if}{/lang}" data-object-id="{@$languageServer->languageServerID}"></span>
							<a href="{link controller='LanguageServerEdit' id=$languageServer->languageServerID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							
							<span class="icon icon16 fa-times jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$languageServer->languageServerID}" data-confirm-message="{lang}wcf.acp.languageServer.delete.sure{/lang}"></span>
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnLanguageServerID">{@$languageServer->languageServerID}</td>
						<td class="columnTitle columnServerURL">
							<a href="{link controller='LanguageServerEdit' id=$languageServer->languageServerID}{/link}">{$languageServer->serverURL}</a>
						</td>
						
						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		<nav>
			<ul>
				<li><a href="{link controller='LanguageServerAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.languageServer.add{/lang}</span></a></li>
				
				{event name='contentNavigationButtonsBottom'}
			</ul>
		</nav>
	</div>
{/if}

{include file='footer'}
