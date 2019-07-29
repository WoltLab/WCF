{include file='header' pageTitle='wcf.acp.menu.link.userTrophy.'|concat:$action}

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/ItemList/User'], function(UiItemListUser) {
		{if $action == 'add'}
			UiItemListUser.init('user', {
				maxItems: 25
			});
		{/if}
		
		elBySel('input[name=useCustomDescription]').addEventListener('click', function () {
			if (elBySel('input[name=useCustomDescription]').checked) {
				elById('userTrophyDescriptionDL').style.display = 'block';
			}
			else {
				elById('userTrophyDescriptionDL').style.display = 'none';
			}
		});
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.userTrophy.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserTrophyList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.userTrophy.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

{if $hasSuitableTrophy}
	<form method="post" action="{if $action == 'add'}{link controller='UserTrophyAdd'}{/link}{else}{link controller='UserTrophyEdit' id=$userTrophy->getObjectID()}{/link}{/if}">
		<div class="section">
			<dl{if $errorField == 'user'} class="formError"{/if}>
				<dt><label for="user">{lang}wcf.acp.trophy.userTrophy.user{/lang}</label></dt>
				<dd>
					{if $action == 'edit'}
						<a href="{link controller='UserEdit' id=$userTrophy->userID}{/link}">{$userTrophy->getUserProfile()->getUsername()}</a>
					{else}
						<input id="user" name="user" type="text" value="{$user}"{if $action == 'edit'} disabled{/if}>
						{if $errorField == 'user'}
							<small class="innerError">
								{if $errorType|is_array}
									{foreach from=$errorType item='errorData'}
										{lang}wcf.acp.trophy.userTrophy.user.error.{@$errorData.type}{/lang}
									{/foreach}
								{elseif $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.trophy.userTrophy.user.description{/lang}</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'trophyID'} class="formError"{/if}>
				<dt><label for="trophyID">{lang}wcf.acp.trophy{/lang}</label></dt>
				<dd>
					{if $action == 'edit'}
						<a href="{link controller='TrophyEdit' id=$userTrophy->trophyID}{/link}">{$userTrophy->getTrophy()->getTitle()}</a>
					{else}
						<select name="trophyID" id="trophyID"{if $action == 'edit'} disabled{/if}>
							<option value="0">{lang}wcf.global.noSelection{/lang}</option>
							
							{foreach from=$trophyCategories item=category}
								<optgroup label="{$category->getTitle()}">
									{foreach from=$category->getTrophies(true) item=trophy}
										<option value="{@$trophy->trophyID}"{if $trophy->trophyID == $trophyID} selected{/if}{if $trophy->awardAutomatically} disabled{/if}>{$trophy->getTitle()}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
						{if $errorField == 'trophyID'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.trophy.userTrophy.trophy.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.trophy.userTrophy.description{/lang}</small>
					{/if}
				</dd>
			</dl>
			
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" name="useCustomDescription" value="1"{if $useCustomDescription} checked{/if}> {lang}wcf.acp.trophy.userTrophy.useCustomDescription{/lang}</label>
				</dd>
			</dl>
			
			<dl id="userTrophyDescriptionDL"{if $errorField == 'description'} class="formError"{/if}{if !$useCustomDescription} style="display: none;"{/if}>
				<dt><label for="description">{lang}wcf.acp.trophy.description{/lang}</label></dt>
				<dd>
					<input id="description" name="description" type="text" value="{$i18nPlainValues[description]}">
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
			
			{event name='dataFields'}
		</div>
	
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
{else}
	<p class="error">{lang}wcf.acp.trophy.error.noSuitableTrophies{/lang}</p>
{/if}

{include file='footer'}
