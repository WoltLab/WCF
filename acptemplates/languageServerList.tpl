{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		{if $__wcf->getSession()->getPermission('admin.language.canDeleteServer')}new WCF.Action.Delete('wcf\\data\\language\\server\\LanguageServerAction', $('.jsLanguageServerRow'));{/if}
		{if $__wcf->getSession()->getPermission('admin.language.canEditServer')}new WCF.Action.Toggle('wcf\\data\\language\\server\\LanguageServerAction', $('.jsLanguageServerRow'));{/if}
	});
	//]]>
</script>

<header class="box48 boxHeadline">
	<img src="{@RELATIVE_WCF_DIR}icon/language1.svg" alt="" class="icon48" />
	<hgroup>
		<h1>{lang}wcf.acp.languageServer.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='LanguageServerList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{if $__wcf->getSession()->getPermission('admin.language.canAddServer')}
		<nav>
			<ul>
				<li><a href="{link controller='LanguageServerAdd'}{/link}" title="{lang}wcf.acp.languageServer.add{/lang}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.languageServer.add{/lang}</span></a></li>
				
				{event name='largeButtons'}
			</ul>
		</nav>
	{/if}
</div>

{if !$languageServers|count}
	<p class="warning">{lang}wcf.acp.languageServer.view.noneAvailable{/lang}</p>
{else}
	<div class="tabularBox tabularBoxTitle marginTop shadow">
		<hgroup>
			<h1>{lang}wcf.acp.languageServer.list{/lang} <span class="badge" title="{lang}wcf.acp.languageServer.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
	
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLanguageServerID{if $sortField == 'languageServerID'} active{/if}" colspan="2"><a href="{link controller='LanguageServerList'}pageNo={@$pageNo}&sortField=languageServerID&sortOrder={if $sortField == 'languageServerID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'languageServerID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnServerURL{if $sortField == 'serverURL'} active{/if}"><a href="{link controller='LanguageServerList'}pageNo={@$pageNo}&sortField=serverURL&sortOrder={if $sortField == 'serverURL' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.languageServer.serverURL{/lang}{if $sortField == 'serverURL'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$languageServers item=languageServer}
					<tr class="jsLanguageServerRow">
						<td class="columnIcon">
							{if $__wcf->getSession()->getPermission('admin.language.canEditServer')}
								<img src="{@RELATIVE_WCF_DIR}icon/{if !$languageServer->disabled}enabled{else}disabled{/if}1.svg" alt="" title="{lang}wcf.global.button.{if !$languageServer->disabled}disable{else}enable{/if}{/lang}" class="icon16 jsToggleButton jsTooltip" data-object-id="{@$languageServer->languageServerID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" />
								<a href="{link controller='LanguageServerEdit' id=$languageServer->languageServerID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16jsTooltip" /></a>
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/{if !$languageServer->disabled}enabled{else}disabled{/if}1D.svg" alt="" title="{lang}wcf.global.button.{if !$languageServer->disabled}disable{else}enable{/if}{/lang}" class="icon16" />
								<img src="{@RELATIVE_WCF_DIR}icon/edit1D.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16" />
							{/if}
							
							{if $__wcf->getSession()->getPermission('admin.language.canDeleteServer')}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsDeleteButton jsTooltip" data-object-id="{@$languageServer->languageServerID}" data-confirm-message="{lang}wcf.acp.languageServer.delete.sure{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16" />
							{/if}
						</td>
						<td class="columnID columnLanguageServerID">{@$languageServer->languageServerID}</td>
						<td class="columnTitle columnServerURL">
							{if $__wcf->getSession()->getPermission('admin.language.canEditServer')}
								<a href="{link controller='LanguageServerEdit' id=$languageServer->languageServerID}{/link}">{@$languageServer->serverURL}</a>
							{else}
								{@$languageServer->serverURL}
							{/if}
						</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		{if $__wcf->getSession()->getPermission('admin.language.canAddServer')}
			<nav>
				<ul>
					<li><a href="{link controller='LanguageServerAdd'}{/link}" title="{lang}wcf.acp.languageServer.add{/lang}" class="button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" class="icon16" /> <span>{lang}wcf.acp.languageServer.add{/lang}</span></a></li>
					
					{event name='largeButtons'}
				</ul>
			</nav>
		{/if}
	</div>
{/if}

{include file='footer'}
