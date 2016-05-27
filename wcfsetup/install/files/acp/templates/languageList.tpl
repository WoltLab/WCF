{include file='header' pageTitle='wcf.acp.language.list'}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\language\\LanguageAction', '.jsLanguageRow');
		new WCF.Action.SimpleProxy({
			action: 'setAsDefault',
			className: 'wcf\\data\\language\\LanguageAction',
			elements: $('.jsLanguageRow .jsSetAsDefaultButton')
		}, {
			success: function(data, statusText, jqXHR) {
				window.location.reload();
			}
		});
		new WCF.Action.Toggle('wcf\\data\\language\\LanguageAction', $('.jsLanguageRow'));
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.list{/lang}</h1>
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
		<table class="table">
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
			
			<tbody>
				{foreach from=$objects item=language}
					<tr class="jsLanguageRow">
						<td class="columnIcon">
							<a href="{link controller='LanguageExport' id=$language->languageID}{/link}" title="{lang}wcf.acp.language.export{/lang}" class="jsTooltip"><span class="icon icon16 fa-download"></span></a>
							
							{if !$language->isDefault}
								<span class="icon icon16 fa-{if !$language->isDisabled}check-{/if}square-o jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $language->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$language->languageID}"></span>
								<span class="icon icon16 fa-check-circle jsSetAsDefaultButton jsTooltip pointer" title="{lang}wcf.acp.language.setAsDefault{/lang}" title="{lang}wcf.acp.language.setAsDefault{/lang}" data-object-id="{@$language->languageID}"></span>
							{else}
								<span class="icon icon16 fa-{if !$language->isDisabled}check-{/if}square-o disabled" title="{lang}wcf.global.button.{if $language->isDisabled}enable{else}disable{/if}{/lang}"></span>
								<span class="icon icon16 fa-check-circle disabled" title="{lang}wcf.acp.language.setAsDefault{/lang}"></span>
							{/if}
							
							<a href="{link controller='LanguageEdit' id=$language->languageID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 fa-pencil"></span></a>
							
							{if !$language->isDefault}
								<span class="icon icon16 fa-times jsTooltip jsDeleteButton pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$language->languageID}" data-confirm-message-html="{lang __encode=true}wcf.acp.language.delete.sure{/lang}"></span>
							{else}
								<span class="icon icon16 fa-times disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
							{/if}
							
							{event name='rowButtons'}
						</td>
						<td class="columnID columnLanguageID">{@$language->languageID}</td>
						<td class="columnTitle columnLanguageName"><a href="{link controller='LanguageEdit' id=$language->languageID}{/link}">{$language->languageName} ({@$language->languageCode})</a></td>
						<td class="columnDigits columnUsers">{#$language->users}</td>
						<td class="columnDigits columnVariables"><a href="{link controller='LanguageItemList' id=$language->languageID}{/link}">{#$language->variables}</a></td>
						<td class="columnDigits columnCustomVariables">{if $language->customVariables > 0}<a href="{link controller='LanguageItemList' id=$language->languageID}hasCustomValue=1{/link}">{#$language->customVariables}</a>{else}{#$language->customVariables}{/if}</td>
						
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
