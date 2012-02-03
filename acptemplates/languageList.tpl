{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\language\\LanguageAction', $('.languageRow'));
		new WCF.Action.SimpleProxy({
			action: 'setAsDefault',
			className: 'wcf\\data\\language\\LanguageAction',
			elements: $('.languageRow .setAsDefaultButton')
		}, {
			success: function(data, statusText, jqXHR) {
				$('.languageRow').each(function(index, row) {
					var $button = $(row).find('.setAsDefaultButton');
					
					if (WCF.inArray($($button).data('objectID'), data.objectIDs)) {
						$($button).attr('src', '{@RELATIVE_WCF_DIR}icon/defaultDisabledS.png');
						$(row).find('.deleteButton').attr('src', '{@RELATIVE_WCF_DIR}icon/deleteDisabledS.png');
					}
					else {
						$($button).attr('src', '{@RELATIVE_WCF_DIR}icon/defaultS.png');
						$(row).find('.deleteButton').attr('src', '{@RELATIVE_WCF_DIR}icon/deleteS.png');
					}
				});
			}
		});
	});
	//]]>
</script>

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/language1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.language.list{/lang}</h1>
	</hgroup>
</header>

<div class="wcf-contentHeader">
	{pages print=true assign=pagesLinks controller='LanguageList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{if $__wcf->getSession()->getPermission('admin.language.canAddLanguage')}
		<nav>
			<ul class="wcf-largeButtons">
				<li><a href="{link controller='LanguageAdd'}{/link}" title="{lang}wcf.acp.language.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
				
				{event name='largeButtons'}
			</ul>
		</nav>
	{/if}
</div>

{if $objects|count}
	<div class="wcf-border wcf-boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.language.list{/lang} <span class="wcf-badge" title="{lang}wcf.acp.language.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
	
		<table>
			<thead>
				<tr>
					<th class="columnID columnLanguageID{if $sortField == 'languageID'} active{/if}" colspan="2"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageID&sortOrder={if $sortField == 'languageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'languageID'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnLanguageName{if $sortField == 'languageName'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageName&sortOrder={if $sortField == 'languageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.name{/lang}{if $sortField == 'languageName'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnUsers{if $sortField == 'users'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=users&sortOrder={if $sortField == 'users' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.users{/lang}{if $sortField == 'users'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnVariables{if $sortField == 'variables'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=variables&sortOrder={if $sortField == 'variables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.variables{/lang}{if $sortField == 'variables'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnCustomVariables{if $sortField == 'customVariables'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=customVariables&sortOrder={if $sortField == 'customVariables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.customVariables{/lang}{if $sortField == 'customVariables'} <img src="{@RELATIVE_WCF_DIR}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$objects item=language}
				<tr class="languageRow">
					<td class="columnIcon">
						{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
							<a href="{link controller='LanguageExport' id=$language->languageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/download1.svg" alt="" title="{lang}wcf.acp.language.export{/lang}" class="balloonTooltip" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/download1D.png" alt="" title="{lang}wcf.acp.language.export{/lang}" />
						{/if}
						
						{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
							{if !$language->isDefault}
								<img src="{@RELATIVE_WCF_DIR}icon/default1.svg" alt="" title="{lang}wcf.acp.language.setAsDefault{/lang}" class="setAsDefaultButton balloonTooltip" data-objectID="{@$language->languageID}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/default1D.svg" alt="" title="{lang}wcf.acp.language.setAsDefault{/lang}" class="setAsDefaultButton" data-objectID="{@$language->languageID}" />
							{/if}
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/default1D.svg" alt="" title="{lang}wcf.acp.language.setAsDefault{/lang}" />
						{/if}
						
						{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
							<a href="{link controller='LanguageEdit' id=$language->languageID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="balloonTooltip" /></a>
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/edit1D.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" />
						{/if}
						{if $__wcf->getSession()->getPermission('admin.language.canDeleteLanguage')}
							{if !$language->isDefault}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="balloonTooltip deleteButton" data-objectID="{@$language->languageID}" data-confirmMessage="{lang}wcf.acp.language.delete.sure{/lang}" />
							{else}
								<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="deleteButton" data-objectID="{@$language->languageID}" data-confirmMessage="{lang}wcf.acp.language.delete.sure{/lang}" />
							{/if}
						{else}
							<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" />
						{/if}
					</td>
					<td class="columnID columnLanguageID">{@$language->languageID}</td>
					<td class="columnTitle columnLanguageName">
						{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
							<a href="{link controller='LanguageEdit' id=$language->languageID}{/link}">{$language->languageName} ({@$language->languageCode})</a>
						{else}
							{$language->languageName} ({@$language->languageCode})
						{/if}
					</td>
					<td class="columnDigits columnUsers">{#$language->users}</td>
					<td class="columnDigits columnVariables">{#$language->variables}</td>
					<td class="columnDigits columnCustomVariables">{if $language->customVariables > 0 && $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}<a href="{link controller='LanguageEdit' id=$language->languageID}customVariables=1{/link}">{#$language->customVariables}</a>{else}{#$language->customVariables}{/if}</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>

	<div class="wcf-contentFooter">
		{@$pagesLinks}
		
		{if $__wcf->getSession()->getPermission('admin.language.canAddLanguage')}
			<nav>
				<ul class="wcf-largeButtons">
					<li><a href="{link controller='LanguageAdd'}{/link}" title="{lang}wcf.acp.language.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
					
					{event name='largeButtons'}
				</ul>
			</nav>
		{/if}
	</div>
{/if}

{include file='footer'}
