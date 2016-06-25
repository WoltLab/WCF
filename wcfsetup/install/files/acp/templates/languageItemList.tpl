{include file='header' pageTitle="wcf.acp.language.item.list"}

<script data-relocate="true" src="{@$__wcf->getPath()}acp/js/WCF.ACP.Language.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.ACP.Language.ItemList();
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.language.item.list{/lang}</h1>
	</div>
	
	{hascontent}
		<nav class="contentHeaderNavigation">
			<ul>
				{content}{event name='contentHeaderNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</header>

{include file='formError'}

<form method="post" action="{link controller='LanguageItemList'}{/link}" id="languageItemSearchForm">
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.global.filter{/lang}</h2>
		
		<div class="row rowColGap formGrid">
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="languageID" id="languageID">
						<option value="0">{lang}wcf.user.language{/lang}</option>
						{foreach from=$availableLanguages item=availableLanguage}
							<option value="{@$availableLanguage->languageID}"{if $availableLanguage->languageID == $languageID} selected{/if}>{$availableLanguage->languageName} ({$availableLanguage->languageCode})</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<select name="languageCategoryID" id="languageCategoryID">
						<option value="0">{lang}wcf.acp.language.category{/lang}</option>
						{foreach from=$availableLanguageCategories item=availableLanguageCategory}
							<option value="{@$availableLanguageCategory->languageCategoryID}"{if $availableLanguageCategory->languageCategoryID == $languageCategoryID} selected{/if}>{$availableLanguageCategory->languageCategory}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="languageItem" name="languageItem" value="{$languageItem}" placeholder="{lang}wcf.global.name{/lang}" class="long">
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="languageItemValue" name="languageItemValue" value="{$languageItemValue}" placeholder="{lang}wcf.acp.language.item.value{/lang}" class="long">
					<label><input type="checkbox" name="hasCustomValue" value="1"{if $hasCustomValue == 1} checked{/if}> {lang}wcf.acp.language.item.customValues{/lang}</label>
					<label><input type="checkbox" name="hasDisabledCustomValue" value="1"{if $hasDisabledCustomValue == 1} checked{/if}> {lang}wcf.acp.language.item.disabledCustomValues{/lang}</label>
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</section>
</form>

{hascontent}
	<div class="paginationTop">
		{content}
			{assign var='linkParameters' value=''}
			{if $languageID}{capture append=linkParameters}&languageID={@$languageID}{/capture}{/if}
			{if $languageCategoryID}{capture append=linkParameters}&languageCategoryID={@$languageCategoryID}{/capture}{/if}
			{if $languageItem}{capture append=linkParameters}&languageItem={@$languageItem|rawurlencode}{/capture}{/if}
			{if $languageItemValue}{capture append=linkParameters}&languageItemValue={@$languageItemValue|rawurlencode}{/capture}{/if}
			{if $hasCustomValue}{capture append=linkParameters}&hasCustomValue=1{/capture}{/if}
			
			{pages print=true assign=pagesLinks controller="LanguageItemList" link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder$linkParameters"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnTitle columnLanguageItem{if $sortField == 'languageItem'} active {@$sortOrder}{/if}"><a href="{link controller='LanguageItemList'}pageNo={@$pageNo}&sortField=languageItem&sortOrder={if $sortField == 'languageItem' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{@$linkParameters}{/link}">{lang}wcf.global.name{/lang}</a></th>
					<th class="columnText columnLanguageItemValue">{lang}wcf.acp.language.item.value{/lang}</th>
					<th class="columnText columnLanguageCustomItemValue">{lang}wcf.acp.language.item.customValue{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{foreach from=$objects item=item}
					<tr>
						<td class="columnTitle columnLanguageItem"><a class="jsLanguageItem" data-language-item-id="{@$item->languageItemID}">{$item->languageItem}</a></td>
						<td class="columnText columnLanguageItemValue">{$item->languageItemValue|truncate:255}</td>
						<td class="columnText columnLanguageCustomItemValue">{if !$item->languageUseCustomValue}<s>{/if}{$item->languageCustomItemValue|truncate:255}{if !$item->languageUseCustomValue}</s>{/if}</td>
						
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
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
