{include file='header' pageTitle='wcf.acp.menu.link.trophy.'|concat:$action}

{include file='shared_colorPickerJavaScript'}
{include file='shared_fontAwesomeJavaScript'}

<script data-relocate="true">
	require(['Language', 'WoltLabSuite/Core/Acp/Ui/Trophy/Editor'], (Language, { setup }) => {
		Language.addObject({
			'wcf.acp.style.image.error.invalidExtension': '{jslang}wcf.acp.style.image.error.invalidExtension{/jslang}',
			'wcf.acp.trophy.badge.edit': '{jslang}wcf.acp.trophy.badge.edit{/jslang}',
			'wcf.acp.trophy.imageUpload.error.notSquared': '{jslang}wcf.acp.trophy.imageUpload.error.notSquared{/jslang}',
			'wcf.acp.trophy.imageUpload.error.tooSmall': '{jslang}wcf.acp.trophy.imageUpload.error.tooSmall{/jslang}',
			'wcf.acp.trophy.imageUpload.error.noImage': '{jslang}wcf.acp.trophy.imageUpload.error.noImage{/jslang}'
		});
		
		setup();
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.trophy.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='TrophyList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.trophy.list{/lang}</span></a></li>

			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

{if $trophyCategories|count}
	<form id="trophyForm" method="post" action="{if $action == 'add'}{link controller='TrophyAdd'}{/link}{else}{link controller='TrophyEdit' id=$trophy->getObjectID()}{/link}{/if}">
		<section class="section">
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{lang}wcf.global.title{/lang}</label></dt>
				<dd>
					<input id="title" name="title" type="text" value="{$i18nPlainValues[title]}" class="medium">
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
			{include file='shared_multipleLanguageInputJavascript' elementIdentifier='title' forceSelection=false}

			<dl{if $errorField == 'description'} class="formError"{/if}>
				<dt><label for="description">{lang}wcf.acp.trophy.description{/lang}</label></dt>
				<dd>
					<input id="description" name="description" type="text" value="{$i18nPlainValues[description]}" class="long">
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
			{include file='shared_multipleLanguageInputJavascript' elementIdentifier='description' forceSelection=false}
			
			<dl id="trophyUseHtmlDL">
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="trophyUseHtml" value="1"{if $trophyUseHtml} checked{/if}> {lang}wcf.acp.trophy.trophyUseHtml{/lang}</label>
				</dd>
			</dl>
			
			<dl{if $errorField == 'categoryID'} class="formError"{/if}>
				<dt><label for="categoryID">{lang}wcf.global.category{/lang}</label></dt>
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
				<dt><label for="showOrder">{lang}wcf.global.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" id="showOrder" name="showOrder" value="{$showOrder}" class="tiny" min="0">
					<small>{lang}wcf.acp.trophy.showOrder.description{/lang}</small>
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
			
			<dl id="revokeAutomaticallyDL"{if !$awardAutomatically} class="disabled"{/if}>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="revokeAutomatically" value="1"{if $revokeAutomatically && $awardAutomatically} checked{/if}{if !$awardAutomatically} disabled{/if}> {lang}wcf.acp.trophy.revokeAutomatically{/lang}</label>
				</dd>
			</dl>
			
			<dl>
				<dt><label for="type">{lang}wcf.acp.trophy.type{/lang}</label></dt>
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
		
		<section id="imageContainer" class="section"{if $type == 2} hidden{/if}>
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.trophy.type.imageUpload{/lang}</h2>
			</header>
	
			<dl{if $errorField == 'imageUpload'} class="formError"{/if}>
				<dt>{lang}wcf.acp.trophy.type.imageUpload{/lang}</dt>
				<dd>
					<input type="hidden" name="tmpHash" value="{$tmpHash}" />
					<div class="row">
						<div class="col-md-6">
							<div id="uploadIconFileButton"></div>
							{if $errorField == 'imageUpload'}
								<small class="innerError">
									{if $errorType == 'empty'}
										{lang}wcf.global.form.error.empty{/lang}
									{/if}
								</small>
							{/if}
							<small>{lang}wcf.acp.trophy.type.imageUpload.description{/lang}</small>
						</div>
						<div class="col-md-6">
							<div id="uploadIconFileContent">{if $action == 'add'}{if !$uploadedImageURL|empty}<img src="{$uploadedImageURL}">{/if}{else}{if $trophy->type == 1}<img src="{$__wcf->getPath()}images/trophy/{$trophy->iconFile}">{/if}{/if}</div>
						</div>
					</div>
	
					<script data-relocate="true">
						require(['WoltLabSuite/Core/Acp/Ui/Trophy/Upload'], function(IconUpload) {
							new IconUpload({if $action == 'add'}0{else}{$trophy->trophyID}{/if}, '{$tmpHash}');
						});
					</script>
				</dd>
			</dl>
		</section>
		
		<section id="badgeContainer" class="section"{if $type == 1} hidden{/if}>
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.trophy.type.badge{/lang}</h2>
			</header>
	
			<dl>
				<dt>{lang}wcf.acp.trophy.type.badge{/lang}</dt>
				<dd>
					<span class="trophyIcon" style="color: {$iconColor}; background-color: {$badgeColor}">
						{@$icon->toHtml(64)}
					</span>
					<button type="button" class="button small trophyIconEditButton" data-value="icon">{lang}wcf.global.fontAwesome.selectIcon{/lang}</button>
					<button type="button" class="button small trophyIconEditButton" data-value="color">{lang}wcf.style.colorPicker.changeColor{/lang}</button>
					<button type="button" class="button small trophyIconEditButton" data-value="background-color">{lang}wcf.style.colorPicker.changeBackgroundColor{/lang}</button>

					
					<input type="hidden" name="iconName" value="{$iconName}">
					<input type="hidden" name="iconColor" value="{$iconColor}">
					<input type="hidden" name="badgeColor" value="{$badgeColor}">
				</dd>
			</dl>
		</section>
		
		{event name='sections'}
		
		<section class="section conditionSection"{if !$awardAutomatically} hidden{/if}>
			<header class="sectionHeader">
				<h2 class="sectionTitle">{lang}wcf.acp.trophy.conditions{/lang}</h2>
				<p class="sectionDescription">{lang}wcf.acp.trophy.conditions.description{/lang}</p>
			</header>
			
			{if $errorField == 'conditions'}
				<woltlab-core-notice type="error">{lang}wcf.acp.trophy.conditions.error.noConditions{/lang}</woltlab-core-notice>
			{/if}

			{include file='shared_userConditions'}
		</section>
		
		{event name='conditionSections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{csrfToken}
		</div>
	</form>
{else}
	<woltlab-core-notice type="error">{lang}wcf.acp.trophy.error.noCategories{/lang}</woltlab-core-notice>
{/if}

<div id="trophyIconEditor" style="display: none;">
	<div class="box128">
		<span class="jsTrophyIcon trophyIcon" style="color: {$iconColor}; background-color: {$badgeColor}">
			{@$icon->toHtml(128)}
		</span>
		<div>
			<dl>
				<dt>{lang}wcf.acp.trophy.badge.iconName{/lang}</dt>
				<dd>
					<span class="jsTrophyIconName">{$iconName}</span>
					<button type="button" class="button small">{icon name='magnifying-glass'}</button>
				</dd>
			</dl>
			
			<dl id="jsIconColorContainer">
				<dt>{lang}wcf.acp.trophy.badge.iconColor{/lang}</dt>
				<dd>
					<span class="colorBox">
						<span id="iconColorValue" class="colorBoxValue jsColorPicker" data-store="iconColorValue"></span>
						<input type="hidden" id="iconColorValue">
					</span>
					<button type="button" class="button small jsButtonIconColorPicker">{icon name='paintbrush'}</button>
				</dd>
			</dl>
			
			<dl id="jsBadgeColorContainer">
				<dt>{lang}wcf.acp.trophy.badge.badgeColor{/lang}</dt>
				<dd>
					<span class="colorBox">
						<span id="badgeColorValue" class="colorBoxValue jsColorPicker" data-store="badgeColorValue"></span>
						<input type="hidden" id="badgeColorValue">
					</span>
					<button type="button" id="test" class="button small jsButtonBadgeColorPicker">{icon name='paintbrush'}</button>
				</dd>
			</dl>
		</div>
	</div>

	<div class="formSubmit">
		<button type="button" class="button buttonPrimary">{lang}wcf.global.button.save{/lang}</button>
	</div>
</div>

{include file='footer'}
