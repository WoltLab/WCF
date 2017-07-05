{include file='header' pageTitle='wcf.acp.menu.link.trophy.'|concat:$action}

{js application='wcf' file='WCF.ColorPicker' bundle='WCF.Combined'}
{include file='fontAwesomeJavaScript'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Trophy/Badge'], function (Language, BadgeHandler) {
		Language.addObject({
			'wcf.style.colorPicker': '{lang}wcf.style.colorPicker{/lang}',
			'wcf.style.colorPicker.new': '{lang}wcf.style.colorPicker.new{/lang}',
			'wcf.style.colorPicker.current': '{lang}wcf.style.colorPicker.current{/lang}',
			'wcf.style.colorPicker.button.apply': '{lang}wcf.style.colorPicker.button.apply{/lang}',
			'wcf.acp.style.image.error.invalidExtension': '{lang}wcf.acp.style.image.error.invalidExtension{/lang}',
			'wcf.acp.trophy.badge.edit': '{lang}wcf.acp.trophy.badge.edit{/lang}'
		});
		
		elBySel('select[name=type]').addEventListener('change', function () {
			if (elBySel('select[name=type]').value == 1) {
				elById('imageContainer').style.display = 'block';
				elById('badgeContainer').style.display = 'none';
			} 
			else if (elBySel('select[name=type]').value == 2) {
				elById('imageContainer').style.display = 'none';
				elById('badgeContainer').style.display = 'block';
			}
		});
		
		elBySel('input[name=awardAutomatically]').addEventListener('change', function () {
			if (elBySel('input[name=awardAutomatically]').checked) {
				elBySel('.conditionSection').style.display = 'block';
			} 
			else {
				elBySel('.conditionSection').style.display = 'none';
			}
		});
		
		BadgeHandler.init(); 
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.trophy.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='TrophyList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.trophy.list{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form id="adForm" method="post" action="{if $action == 'add'}{link controller='TrophyAdd'}{/link}{else}{link controller='TrophyEdit' id=$trophy->getObjectID()}{/link}{/if}">
	<section>
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
			<dd>
				<input id="title" name="title" type="text" value="{$i18nPlainValues[title]}">
				{if $errorField == 'title'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}
		
		<dl{if $errorField == 'description'} class="formError"{/if}>
			<dt><label for="description">{lang}wcf.acp.trophy.description{/lang}</label></dt>
			<dd>
				<textarea id="description" name="description" cols="40" rows="10">{$i18nPlainValues[description]}</textarea>
				{if $errorField == 'description'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{elseif $errorType == 'multilingual'}
							{lang}wcf.global.form.error.multilingual{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		{include file='multipleLanguageInputJavascript' elementIdentifier='description' forceSelection=false}
		
		<dl{if $errorField == 'categoryID'} class="formError"{/if}>
			<dt><label for="categoryID">{lang}wcf.acp.trophy.category{/lang}</label></dt>
			<dd>
				<select name="categoryID" id="categoryID">
					<option value="0">{lang}wcf.global.noSelection{/lang}</option>

					{foreach from=$trophyCategories item=category}
						<option value="{$category->getObjectID()}"{if $category->getObjectID() == $categoryID} selected{/if}>{$category->getTitle()}</option>
					{/foreach}
				</select>
				{if $errorField == 'categoryID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{/if}
					</small>
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="isDisabled" value="1"{if $isDisabled} checked{/if}> {lang}wcf.acp.trophy.isDisabled{/lang}</label>
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" name="awardAutomatically" value="1"{if $awardAutomatically} checked{/if}> {lang}wcf.acp.trophy.awardAutomatically{/lang}</label>
			</dd>
		</dl>
		
		<dl>
			<dt>{lang}wcf.acp.trophy.type{/lang}</dt>
			<dd>
				<select name="type" id="type">
					{foreach from=$availableTypes item=trophyType key=key}
						<option value="{$key}"{if $type == $key} selected{/if}>{lang}wcf.acp.trophy.type.{$trophyType}{/lang}</option>
					{/foreach}
				</select>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</section>
	
	<section id="imageContainer" class="section"{if $type == 2} style="display: none;"{/if}>
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.trophy.type.image{/lang}</h2>
		</header>

		{* @TODO *}
	</section>
	
	<section id="badgeContainer" class="section"{if $type == 1} style="display: none;"{/if}>
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.trophy.type.badge{/lang}</h2>
		</header>

		<dl>
			<dt>{lang}wcf.acp.trophy.type.badge{/lang}</dt>
			<dd>
				<span class="icon icon64 fa-{$iconName} jsTrophyIcon trophyIcon" style="color: {$iconColor}; background-color: {$badgeColor}"></span>
				<button class="small">{lang}wcf.global.button.edit{/lang}</button>
				
				<input type="hidden" name="iconName" value="{$iconName}">
				<input type="hidden" name="iconColor" value="{$iconColor}">
				<input type="hidden" name="badgeColor" value="{$badgeColor}">
			</dd>
		</dl>
	</section>
	
	{event name='sections'}
	
	<section class="section conditionSection"{if !$awardAutomatically} style="display: none;"{/if}>
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.trophy.conditions{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.trophy.conditions.description{/lang}</p>
		</header>
		
		{if $errorField == 'conditions'}
			<p class="error">{lang}wcf.acp.trophy.conditions.error.noConditions{/lang}</p>
		{/if}
		
		{include file='userConditions'}
	</section>
	
	{event name='conditionSections'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

<div id="trophyIconEditor" style="display: none;">
	<div class="box128">
		<span class="icon icon144 fa-{$iconName} jsTrophyIcon trophyIcon" style="color: {$iconColor}; background-color: {$badgeColor}"></span>
		<div>
			<dl>
				<dt>{lang}wcf.acp.trophy.badge.iconName{/lang}</dt>
				<dd>
					<span class="jsTrophyIconName">{$iconName}</span>
					<a href="#" class="button small"><span class="icon icon16 fa-search"></span></a>
				</dd>
			</dl>
			
			<dl id="jsIconColorContainer">
				<dt>{lang}wcf.acp.trophy.badge.iconColor{/lang}</dt>
				<dd>
					<span class="colorBox">
						<span id="iconColorValue" class="colorBoxValue jsColorPicker" data-store="iconColorValue"></span>
						<input type="hidden" id="iconColorValue">
					</span>
					<a href="#" class="button small jsButtonIconColorPicker"><span class="icon icon16 fa-paint-brush"></span></a>
				</dd>
			</dl>
			
			<dl id="jsBadgeColorContainer">
				<dt>{lang}wcf.acp.trophy.badge.badgeColor{/lang}</dt>
				<dd>
					<span class="colorBox">
						<span id="badgeColorValue" class="colorBoxValue jsColorPicker" data-store="badgeColorValue"></span>
						<input type="hidden" id="badgeColorValue">
					</span>
					<a href="#" id="test" class="button small jsButtonBadgeColorPicker"><span class="icon icon16 fa-paint-brush"></span></a>
				</dd>
			</dl>
		</div>
	</div>

	<div class="formSubmit">
		<button class="buttonPrimary">{lang}wcf.global.button.save{/lang}</button>
	</div>
</div>

{include file='footer'}
