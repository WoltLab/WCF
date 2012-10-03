{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\language\\LanguageAction', $('.jsLanguageRow'), $('#userTableContainer hgroup span.badge'));
		new WCF.Action.SimpleProxy({
			action: 'setAsDefault',
			className: 'wcf\\data\\language\\LanguageAction',
			elements: $('.jsLanguageRow .setAsDefaultButton')
		}, {
			success: function(data, statusText, jqXHR) {
				$('.jsLanguageRow').each(function(index, row) {
					var $button = $(row).find('.jsSetAsDefaultButton');
					
					if (WCF.inArray($($button).data('objectID'), data.objectIDs)) {
						$($button).attr('src', '{@$__wcf->getPath()}icon/default.svg');
						$(row).find('.jsDeleteButton').attr('src', '{@$__wcf->getPath()}icon/delete.svg');
					}
					else {
						$($button).attr('src', '{@$__wcf->getPath()}icon/default1.svg');
						$(row).find('.jsDeleteButton').attr('src', '{@$__wcf->getPath()}icon/delete.svg');
					}
				});
			}
		});
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.language.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller='LanguageList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
	
	{if $__wcf->getSession()->getPermission('admin.language.canAddLanguage')}
		<nav>
			<ul>
				<li><a href="{link controller='LanguageAdd'}{/link}" title="{lang}wcf.acp.language.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
				
				{event name='largeButtons'}
			</ul>
		</nav>
	{/if}
</div>

{if $objects|count}
	<div id="userTableContainer" class="tabularBox tabularBoxTitle marginTop shadow">
		<hgroup>
			<h1>{lang}wcf.acp.language.list{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.language.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLanguageID{if $sortField == 'languageID'} active{/if}" colspan="2"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageID&sortOrder={if $sortField == 'languageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}{if $sortField == 'languageID'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnTitle columnLanguageName{if $sortField == 'languageName'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageName&sortOrder={if $sortField == 'languageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.name{/lang}{if $sortField == 'languageName'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnUsers{if $sortField == 'users'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=users&sortOrder={if $sortField == 'users' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.users{/lang}{if $sortField == 'users'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnVariables{if $sortField == 'variables'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=variables&sortOrder={if $sortField == 'variables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.variables{/lang}{if $sortField == 'variables'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
					<th class="columnDigits columnCustomVariables{if $sortField == 'customVariables'} active{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=customVariables&sortOrder={if $sortField == 'customVariables' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.customVariables{/lang}{if $sortField == 'customVariables'} <img src="{@$__wcf->getPath()}icon/sort{@$sortOrder}.svg" alt="" />{/if}</a></th>
				</tr>
			</thead>
			
			<tbody>
			{foreach from=$objects item=language}
				<tr class="jsLanguageRow">
					<td class="columnIcon">
						{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
							<a href="{link controller='LanguageExport' id=$language->languageID}{/link}"><img src="{@$__wcf->getPath()}icon/download.svg" alt="" title="{lang}wcf.acp.language.export{/lang}" class="icon16 jsTooltip" /></a>
						{else}
							<img src="{@$__wcf->getPath()}icon/download.svg" alt="" title="{lang}wcf.acp.language.export{/lang}" class="icon16 disabloed" />
						{/if}
						
						{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
							{if !$language->isDefault}
								<img src="{@$__wcf->getPath()}icon/default.svg" alt="" title="{lang}wcf.acp.language.setAsDefault{/lang}" class="icon16 jsSetAsDefaultButton jsTooltip" data-object-id="{@$language->languageID}" />
							{else}
								<img src="{@$__wcf->getPath()}icon/default.svg" alt="" title="{lang}wcf.acp.language.setAsDefault{/lang}" class="icon16 disabled" />
							{/if}
						{else}
							<img src="{@$__wcf->getPath()}icon/default.svg" alt="" title="{lang}wcf.acp.language.setAsDefault{/lang}" class="icon16 disabled" />
						{/if}
						
						{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
							<a href="{link controller='LanguageEdit' id=$language->languageID}{/link}"><img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16 jsTooltip" /></a>
						{else}
							<img src="{@$__wcf->getPath()}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="icon16 disabled" />
						{/if}
						{if $__wcf->getSession()->getPermission('admin.language.canDeleteLanguage')}
							{if !$language->isDefault}
								<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 jsTooltip jsDeleteButton" data-object-id="{@$language->languageID}" data-confirm-message="{lang}wcf.acp.language.delete.sure{/lang}" />
							{else}
								<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 disabled" />
							{/if}
						{else}
							<img src="{@$__wcf->getPath()}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="icon16 disabled" />
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
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		{if $__wcf->getSession()->getPermission('admin.language.canAddLanguage')}
			<nav>
				<ul>
					<li><a href="{link controller='LanguageAdd'}{/link}" title="{lang}wcf.acp.language.add{/lang}" class="button"><img src="{@$__wcf->getPath()}icon/add.svg" alt="" class="icon24" /> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
					
					{event name='largeButtons'}
				</ul>
			</nav>
		{/if}
	</div>
{/if}

{include file='footer'}
