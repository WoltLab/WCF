{capture assign='pageTitle'}{lang}wcf.user.option.category.settings.{$category}{/lang} - {lang}wcf.user.menu.settings{/lang}{/capture}

{capture assign='contentTitle'}{lang}wcf.user.menu.settings{/lang}: {lang}wcf.user.option.category.settings.{$category}{/lang}{/capture}

{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{include file='shared_formError'}

{if $success|isset}
	<woltlab-core-notice type="success">{lang}wcf.global.success.edit{/lang}</woltlab-core-notice>
{/if}

<form method="post" action="{$formAction}">
	{if $category == 'general'}
		{if $availableLanguages|count > 1}
			<section class="section" id="section_language">
				<h2 class="sectionTitle">{lang}wcf.user.language{/lang}</h2>
				
				<dl>
					<dt><label>{lang}wcf.user.language.description{/lang}</label></dt>
					<dd id="languageIDContainer">
						<script data-relocate="true">
							require(['WoltLabSuite/Core/Language/Chooser'], ({ init }) => {
								const languages = {
									{implode from=$availableLanguages item=language}
									'{@$language->languageID}': {
										iconPath: '{@$language->getIconPath()|encodeJS}',
										languageName: '{@$language|encodeJS}'
									}
									{/implode}
								};
								
								init('languageIDContainer', 'languageID', {@$languageID}, languages);
							});
						</script>
						<noscript>
							<select name="languageID" id="languageID">
								{foreach from=$availableLanguages item=language}
									<option value="{$language->languageID}"{if $language->languageID == $languageID} selected{/if}>{$language}</option>
								{/foreach}
							</select>
						</noscript>
					</dd>
				</dl>
				
				{hascontent}
					<dl>
						<dt><label>{lang}wcf.user.visibleLanguages{/lang}</label></dt>
						<dd class="floated">
						{content}
							{foreach from=$availableContentLanguages item=language}
								<label><input name="contentLanguageIDs[]" type="checkbox" value="{$language->languageID}"{if $language->languageID|in_array:$contentLanguageIDs} checked{/if}> {$language}</label>
							{/foreach}
						{/content}
						<small>{lang}wcf.user.visibleLanguages.description{/lang}</small></dd>
					</dl>
				{/hascontent}
				
				{event name='languageFields'}
			</section>
		{/if}
		
		<section class="section" id="section_style">
			<h2 class="sectionTitle">{lang}wcf.user.styles{/lang}</h2>
				
			{if $availableStyles|count > 1}
				<dl>
					<dt><label for="styleID">{lang}wcf.user.style{/lang}</label></dt>
					<dd>
						<select id="styleID" name="styleID">
							<option value="0">{lang}wcf.global.defaultValue{/lang}</option>
							{foreach from=$availableStyles item=style}
								<option value="{$style->styleID}"{if $style->styleID == $styleID} selected{/if}>{$style->styleName}</option>
							{/foreach}
						</select>
						<small>{lang}wcf.user.style.description{/lang}</small>
					</dd>
				</dl>
			{/if}
			
			<dl>
				<dt><label for="colorScheme">{lang}wcf.user.style.colorScheme{/lang}</label></dt>
				<dd>
					<select id="colorScheme" name="colorScheme">
						<option value="system"{if $colorScheme === 'system'} selected{/if}>{lang}wcf.style.setColorScheme.system{/lang}</option>
						<option value="light"{if $colorScheme === 'light'} selected{/if}>{lang}wcf.style.setColorScheme.light{/lang}</option>
						<option value="dark"{if $colorScheme === 'dark'} selected{/if}>{lang}wcf.style.setColorScheme.dark{/lang}</option>
					</select>
					<small>{lang}wcf.user.style.colorScheme.description{/lang}</small>
				</dd>
			</dl>
			
			{event name='styleFields'}
		</section>
		
		{if MODULE_TROPHY && $__wcf->getSession()->getPermission('user.profile.trophy.maxUserSpecialTrophies') > 0 && $availableTrophies|count}
			<section class="section" id="section_trophy">
				<h2 class="sectionTitle">{lang}wcf.user.trophy.trophies{/lang}</h2>
				<dl{if $errorField == 'specialTrophies'} class="formError"{/if}>
					<dt>{lang}wcf.user.trophy.specialTrophies{/lang}</dt>
					<dd>
						<ul class="specialTrophyList">
							{if $__wcf->getSession()->getPermission('user.profile.trophy.maxUserSpecialTrophies') == 1}
								{foreach from=$availableTrophies item=trophy}
									<li><label><input type="radio" name="specialTrophies[]" value="{$trophy->getObjectID()}"{if $trophy->getObjectID()|in_array:$specialTrophies} checked{/if}> {@$trophy->renderTrophy(32)} <span>{$trophy->getTitle()}</span></label></li>
								{/foreach}
							{else}
								{foreach from=$availableTrophies item=trophy}
									<li><label><input type="checkbox" name="specialTrophies[]" value="{$trophy->getObjectID()}"{if $trophy->getObjectID()|in_array:$specialTrophies} checked{/if}> {@$trophy->renderTrophy(32)} <span>{$trophy->getTitle()}</span></label></li>
								{/foreach}
							{/if}
						</ul>
						{if $errorField == 'specialTrophies'}
							<small class="innerError">
								{lang}wcf.user.trophy.specialTrophies.error.{$errorType}{/lang}
							</small>
						{/if}
						<small>{lang}wcf.user.trophy.specialTrophies.description{/lang}</small>
					</dd>
				</dl>

				{event name='trophyFields'}
			</section>
		{/if}
	{/if}
	
	{if !$optionTree|empty}
		{foreach from=$optionTree[0][categories][0][categories] item=optionCategory}
			<section class="section" id="optionCategory_{@$optionCategory[object]->categoryName}">
				<h2 class="sectionTitle">{lang}wcf.user.option.category.{@$optionCategory[object]->categoryName}{/lang}</h2>
				
				{include file='userProfileOptionFieldList' options=$optionCategory[options] langPrefix='wcf.user.option.'}
			</section>
		{/foreach}
	{/if}
	
	{event name='sections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{if $category != 'general'}<input type="hidden" name="category" value="{$category}">{/if}
		{csrfToken}
	</div>
</form>

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
	});
</script>

{include file='footer' __disableAds=true}
