{include file='header' pageTitle="wcf.acp.language.item.list"}

<script data-relocate="true" src="{@$__wcf->getPath()}acp/js/WCF.ACP.Language.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.ACP.Language.ItemList({@$count}, {@$pageNo});
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
					<select name="id" id="languageID">
						<option value="0">{lang}wcf.user.language{/lang}</option>
						{foreach from=$availableLanguages item=availableLanguage}
							<option value="{@$availableLanguage->languageID}"{if $availableLanguage->languageID == $languageID} selected="selected"{/if}>{$availableLanguage->languageName} ({$availableLanguage->languageCode})</option>
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
							<option value="{@$availableLanguageCategory->languageCategoryID}"{if $availableLanguageCategory->languageCategoryID == $languageCategoryID} selected="selected"{/if}>{$availableLanguageCategory->languageCategory}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="languageItem" name="languageItem" value="{$languageItem}" placeholder="{lang}wcf.global.name{/lang}" class="long" />
				</dd>
			</dl>
			
			<dl class="col-xs-12 col-md-4">
				<dt></dt>
				<dd>
					<input type="text" id="languageItemValue" name="languageItemValue" value="{$languageItemValue}" placeholder="{lang}wcf.acp.language.item.value{/lang}" class="long" />
					<label><input type="checkbox" name="hasCustomValue" value="1" {if $hasCustomValue == 1}checked="checked" {/if}/> {lang}wcf.acp.language.item.customValues{/lang}</label>
				</dd>
			</dl>
			
			{event name='filterFields'}
		</div>
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</section>
</form>

{if $objects|count}
	<div class="section sectionContainerList">
		<ol class="containerList">
			{foreach from=$objects item=item}
				<li>
					<div>
						<div class="details">
							<div class="containerHeadline">
								<h3><a class="jsLanguageItem" data-language-item-id="{@$item->languageItemID}">{$item->languageItem}</a>{if $item->languageCustomItemValue !== null} <span class="icon icon16 fa-bookmark jsTooltip" title="{lang}wcf.acp.language.item.hasCustomValue{/lang}"></span>{/if}</h3>
							</div>
							
							<p class="containerContent">{if $item->languageUseCustomValue}{$item->languageCustomItemValue|truncate:255}{else}{$item->languageItemValue|truncate:255}{/if}</p>
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
	
	<footer class="contentFooter">
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
