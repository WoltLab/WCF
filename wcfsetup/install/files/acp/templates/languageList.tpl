{include file='header' pageTitle='wcf.acp.language.list'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.list{/lang} <span class="badge badgeInverse">{#$items}</span></h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LanguageAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
			<li><a href="{link controller='LanguageImport'}{/link}" class="button"><span class="icon icon16 fa-upload"></span> <span>{lang}wcf.acp.language.import{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}{pages print=true assign=pagesLinks controller='LanguageList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div id="userTableContainer" class="section tabularBox">
		<table class="table jsObjectActionContainer" data-object-action-class-name="wcf\data\language\LanguageAction">
			<thead>
				<tr>
					<th class="columnID columnLanguageID{if $sortField == 'languageID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageID&sortOrder={if $sortField == 'languageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnLanguageName{if $sortField == 'languageName'} active {@$sortOrder}{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageName&sortOrder={if $sortField == 'languageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnDigits columnUsers{if $sortField == 'users'} active {@$sortOrder}{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=users&sortOrder={if $sortField == 'users' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.users{/lang}</a></th>
					<th class="columnDigits columnVariables{if $sortField == 'variables'} active {@$sortOrder}{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=variables&sortOrder={if $sortField == 'variables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.variables{/lang}</a></th>
					<th class="columnDigits columnCustomVariables{if $sortField == 'customVariables'} active {@$sortOrder}{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=customVariables&sortOrder={if $sortField == 'customVariables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.customVariables{/lang}</a></th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody class="jsReloadPageWhenEmpty">
				{foreach from=$objects item=language}
					<tr class="jsLanguageRow jsObjectActionObject" data-object-id="{@$language->getObjectID()}">
						<td class="columnIcon">
							<a href="{link controller='LanguageExport' id=$language->languageID}{/link}" title="{lang}wcf.acp.language.export{/lang}" class="jsTooltip"><span class="icon icon16 fa-download"></span></a>
							
							{if !$language->isDefault}
								{objectAction action="toggle" isDisabled=$language->isDisabled}
								<span class="icon icon16 fa-check-circle jsObjectAction jsTooltip pointer" data-object-action="setAsDefault" data-object-action-success="reload" title="{lang}wcf.acp.language.setAsDefault{/lang}"></span>
							{else}
								<span class="icon icon16 fa-{if !$language->isDisabled}check-{/if}square-o disabled" title="{lang}wcf.global.button.{if $language->isDisabled}enable{else}disable{/if}{/lang}"></span>
								<span class="icon icon16 fa-check-circle disabled" title="{lang}wcf.acp.language.setAsDefault{/lang}"></span>
							{/if}
							
							<a href="{link controller='LanguageEdit' id=$language->languageID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							
							{if $language->isDeletable()}
								{objectAction action="delete" objectTitle=$language->languageName}
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnLanguageID">{@$language->languageID}</td>
						<td class="columnTitle columnLanguageName"><a href="{link controller='LanguageEdit' id=$language->languageID}{/link}">{$language->languageName} ({@$language->languageCode})</a></td>
						<td class="columnDigits columnUsers">{#$language->users}</td>
						<td class="columnDigits columnVariables"><a href="{link controller='LanguageItemList'}languageID={@$language->languageID}{/link}">{#$language->variables}</a></td>
						<td class="columnDigits columnCustomVariables">{if $language->customVariables > 0}<a href="{link controller='LanguageItemList'}languageID={@$language->languageID}&hasCustomValue=1{/link}">{#$language->customVariables}</a>{else}{#$language->customVariables}{/if}</td>
						
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
				<li><a href="{link controller='LanguageAdd'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
				<li><a href="{link controller='LanguageImport'}{/link}" class="button"><span class="icon icon16 fa-upload"></span> <span>{lang}wcf.acp.language.import{/lang}</span></a></li>
				
				{event name='contentFooterNavigation'}
			</ul>
		</nav>
	</footer>
{/if}

{include file='footer'}
