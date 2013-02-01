{include file='header' pageTitle='wcf.acp.language.list'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\language\\LanguageAction', '.jsLanguageRow');
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
	
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->getSession()->getPermission('admin.language.canAddLanguage')}
						<li><a href="{link controller='LanguageAdd'}{/link}" title="{lang}wcf.acp.language.add{/lang}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
					{/if}
					
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{if $objects|count}
	<div id="userTableContainer" class="tabularBox tabularBoxTitle marginTop">
		<hgroup>
			<h1>{lang}wcf.acp.language.list{/lang} <span class="badge badgeInverse" title="{lang}wcf.acp.language.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnLanguageID{if $sortField == 'languageID'} active {@$sortOrder}{/if}" colspan="2"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageID&sortOrder={if $sortField == 'languageID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnTitle columnLanguageName{if $sortField == 'languageName'} active {@$sortOrder}{/if}"><a href="{link controller='LanguageList'}pageNo={@$pageNo}&sortField=languageName&sortOrder={if $sortField == 'languageName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.language.name{/lang}</a></th>
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
							{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
								<a href="{link controller='LanguageExport' id=$language->languageID}{/link}" title="{lang}wcf.acp.language.export{/lang}" class="jsTooltip"><span class="icon icon16 icon-download-alt"></span></a>
							{/if}
							
							{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
								{if !$language->isDefault}
									<span class="icon icon16 icon-check jsSetAsDefaultButton jsTooltip pointer" title="{lang}wcf.acp.language.setAsDefault{/lang}" title="{lang}wcf.acp.language.setAsDefault{/lang}" data-object-id="{@$language->languageID}"></span>
								{else}
									<span class="icon icon16 icon-check disabled" title="{lang}wcf.acp.language.setAsDefault{/lang}"></span>
								{/if}
							{/if}
							
							{if $__wcf->getSession()->getPermission('admin.language.canEditLanguage')}
								<a href="{link controller='LanguageEdit' id=$language->languageID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
							{/if}
							{if $__wcf->getSession()->getPermission('admin.language.canDeleteLanguage')}
								{if !$language->isDefault}
									<span class="icon icon16 icon-remove jsTooltip jsDeleteButton pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$language->languageID}" data-confirm-message="{lang}wcf.acp.language.delete.sure{/lang}"></span>
								{else}
									<span class="icon icon16 icon-remove disabled" title="{lang}wcf.global.button.delete{/lang}"></span>
								{/if}
							{/if}
							
							{event name='rowButtons'}
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

						{event name='columns'}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	
	<div class="contentNavigation">
		{@$pagesLinks}
		
		{hascontent}
			<nav>
				<ul>
					{content}
						{if $__wcf->getSession()->getPermission('admin.language.canAddLanguage')}
							<li><a href="{link controller='LanguageAdd'}{/link}" title="{lang}wcf.acp.language.add{/lang}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.language.add{/lang}</span></a></li>
						{/if}
						
						{event name='contentNavigationButtonsBottom'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</div>
{/if}

{include file='footer'}
