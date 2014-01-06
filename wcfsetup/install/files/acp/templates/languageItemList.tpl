{include file='header' pageTitle="wcf.acp.language.item.list"}

<script data-relocate="true" src="{@$__wcf->getPath()}acp/js/WCF.ACP.Language.js?v={@$__wcfVersion}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.ACP.Language.ItemList();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.language.item.list{/lang}</h1>
</header>

{include file='formError'}

<form method="post" action="{link controller='LanguageItemList'}{/link}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.filter{/lang}</legend>
			
			<dl>
				<dt><label for="languageID">{lang}wcf.user.language{/lang}</label></dt>
				<dd>
					<select name="id" id="languageID">
						{foreach from=$availableLanguages item=availableLanguage}
							<option value="{@$availableLanguage->languageID}"{if $availableLanguage->languageID == $languageID} selected="selected"{/if}>{$availableLanguage->languageName} ({$availableLanguage->languageCode})</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="languageCategoryID">{lang}wcf.acp.language.category{/lang}</label></dt>
				<dd>
					<select name="languageCategoryID" id="languageCategoryID">
						<option value="0">{lang}wcf.global.noSelection{/lang}</option>
						{foreach from=$availableLanguageCategories item=availableLanguageCategory}
							<option value="{@$availableLanguageCategory->languageCategoryID}"{if $availableLanguageCategory->languageCategoryID == $languageCategoryID} selected="selected"{/if}>{$availableLanguageCategory->languageCategory}</option>
						{/foreach}
					</select>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="languageItem">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="languageItem" name="languageItem" value="{$languageItem}" class="long" />
				</dd>
			</dl>
			
			<dl>
				<dt><label for="languageItemValue">{lang}wcf.acp.language.item.value{/lang}</label></dt>
				<dd>
					<input type="text" id="languageItemValue" name="languageItemValue" value="{$languageItemValue}" class="long" />
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="hasCustomValue" value="1" {if $hasCustomValue == 1}checked="checked" {/if}/> {lang}wcf.acp.language.item.customValues{/lang}</label>
				</dd>
			</dl>
		</fieldset>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<div class="contentNavigation">
	{hascontent}
		<nav>
			{content}
				<ul>
					{event name='contentNavigationButtonsTop'}
				</ul>
			{/content}
		</nav>
	{/hascontent}
</div>

{if $objects|count}
	<div class="container marginTop">
		<ol class="containerList">
			{foreach from=$objects item=item}
				<li>
					<div>
						<div class="details">
							<div class="containerHeadline">
								<h3><a class="jsLanguageItem" data-language-item-id="{@$item->languageItemID}">{$item->languageItem}</a>{if $item->languageCustomItemValue !== null} <span class="icon icon16 icon-bookmark jsTooltip" title="{lang}wcf.acp.language.item.hasCustomValue{/lang}"></span>{/if}</h3>
							</div>
							
							<p>{if $item->languageUseCustomValue}{$item->languageCustomItemValue|truncate:255}{else}{$item->languageItemValue|truncate:255}{/if}</p>
						</div>
					</div>
				</li>
			{/foreach}
		</ol>
	</div>
	
	<div class="contentNavigation">
		{hascontent}
			<nav>
				{content}
					<ul>
						{event name='contentNavigationButtonsBottom'}
					</ul>
				{/content}
			</nav>
		{/hascontent}
	</div>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

{include file='footer'}
