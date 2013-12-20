{include file='header' pageTitle='wcf.acp.group.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
		new WCF.ACP.Options();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.group.{@$action}{/lang}</h1>
</header>

{include file='formError'}

{if $warningSelfEdit|isset}
	<p class="warning">{lang}wcf.acp.group.edit.warning.selfIsMember{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			{if $action == 'edit' && $availableUserGroups|count > 1}
				<li class="dropdown">
					<a class="button dropdownToggle"><span class="icon icon16 icon-sort"></span> <span>{lang}wcf.acp.group.button.choose{/lang}</span></a>
					<div class="dropdownMenu">
						<ul class="scrollableDropdownMenu">
							{foreach from=$availableUserGroups item='availableUserGroup'}
								<li{if $availableUserGroup->groupID == $groupID} class="active"{/if}><a href="{link controller='UserGroupEdit' id=$availableUserGroup->groupID}{/link}">{$availableUserGroup->getName()}</a></li>
							{/foreach}
						</ul>
					</div>
				</li>
			{/if}
			
			<li><a href="{link controller='UserGroupList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.group.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UserGroupAdd'}{/link}{else}{link controller='UserGroupEdit' id=$groupID}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorType.groupName|isset} class="formError"{/if}>
				<dt><label for="groupName">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="groupName" name="groupName" value="{$i18nPlainValues['groupName']}" autofocus="autofocus" class="medium" />
					{if $errorType.groupName|isset}
						<small class="innerError">
							{if $errorType.groupName == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType.groupName == 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}wcf.acp.group.groupName.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
					
					{include file='multipleLanguageInputJavascript' elementIdentifier='groupName' forceSelection=false}
				</dd>
			</dl>
			
			<dl{if $errorType.groupDescription|isset} class="formError"{/if}>
				<dt><label for="groupDescription">{lang}wcf.acp.group.description{/lang}</label></dt>
				<dd>
					<textarea id="groupDescription" name="groupDescription" cols="40" rows="3">{$i18nPlainValues['groupDescription']}</textarea>
					{if $errorType.groupDescription|isset}
						<small class="innerError">
							{lang}wcf.acp.group.description.error.{@$errorType.groupDescription}{/lang}
						</small>
					{/if}
					
					{include file='multipleLanguageInputJavascript' elementIdentifier='groupDescription' forceSelection=false}
				</dd>
			</dl>
			
			<dl{if $errorType.priority|isset} class="formError"{/if}>
				<dt><label for="priority">{lang}wcf.acp.group.priority{/lang}</label></dt>
				<dd>
					<input type="number" id="priority" name="priority" value="{@$priority}" class="tiny" max="8388607" />
					{if $errorType.priority|isset}
						<small class="innerError">
							{lang}wcf.acp.group.priority.error.{@$errorType.priority}{/lang}
						</small>
					{/if}
					<small>{lang}wcf.acp.group.priority.description{/lang}</small>
				</dd>
			</dl>
			
			{if MODULE_USERS_ONLINE}
				<dl{if $errorType.userOnlineMarking|isset} class="formError"{/if}>
					<dt><label for="userOnlineMarking">{lang}wcf.acp.group.userOnlineMarking{/lang}</label></dt>
					<dd>
						<input type="text" id="userOnlineMarking" name="userOnlineMarking" value="{$userOnlineMarking}" class="long" />
						{if $errorType.userOnlineMarking|isset}
							<small class="innerError">
								{lang}wcf.acp.group.userOnlineMarking.error.{@$errorType.userOnlineMarking}{/lang}
							</small>
						{/if}
						<small>{lang}wcf.acp.group.userOnlineMarking.description{/lang}</small>
					</dd>
				</dl>
			{/if}
			
			{if MODULE_TEAM_PAGE && ($action == 'add' || $group->groupType > 3)}
				<dl>
					<dt></dt>
					<dd>
						<label><input type="checkbox" id="showOnTeamPage" name="showOnTeamPage" value="1" {if $showOnTeamPage}checked="checked" {/if}/> {lang}wcf.acp.group.showOnTeamPage{/lang}</label>
					</dd>
				</dl>
			{/if}
			
			{event name='dataFields'}
		</fieldset>
		
		{event name='fieldsets'}
		
		<div class="tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
			<nav class="tabMenu">
				<ul>
					{foreach from=$optionTree item=categoryLevel1}
						<li><a href="{@$__wcf->getAnchor($categoryLevel1[object]->categoryName)}">{lang}wcf.acp.group.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
					{/foreach}
				</ul>
			</nav>
			
			{foreach from=$optionTree item=categoryLevel1}
				<div id="{@$categoryLevel1[object]->categoryName}" class="container containerPadding tabMenuContainer tabMenuContent">
					<nav class="menu">
						<ul>
							{foreach from=$categoryLevel1[categories] item=$categoryLevel2}
								{assign var=__categoryLevel2Name value=$categoryLevel1[object]->categoryName|concat:'-':$categoryLevel2[object]->categoryName}
								<li><a href="{@$__wcf->getAnchor($__categoryLevel2Name)}">{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</a></li>
							{/foreach}
						</ul>
					</nav>
					
					{foreach from=$categoryLevel1[categories] item=categoryLevel2}
						<div id="{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}" class="hidden">
							{if $categoryLevel2[options]|count}
								<fieldset>
									<legend>{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</legend>
									{hascontent}<small>{content}{lang __optional=true}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}.description{/lang}{/content}</small>{/hascontent}
								
									{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.acp.group.option.'}
								</fieldset>
							{/if}
							
							{if $categoryLevel2[categories]|count}
								{foreach from=$categoryLevel2[categories] item=categoryLevel3}
									<fieldset>
										<legend>{lang}wcf.acp.group.option.category.{@$categoryLevel3[object]->categoryName}{/lang}</legend>
										{hascontent}<small>{content}{lang __optional=true}wcf.acp.group.option.category.{@$categoryLevel3[object]->categoryName}.description{/lang}{/content}</small>{/hascontent}
										
										{include file='optionFieldList' options=$categoryLevel3[options] langPrefix='wcf.acp.group.option.'}
									</fieldset>
								{/foreach}
							{/if}
						</div>
					{/foreach}
				</div>
			{/foreach}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="action" value="{@$action}" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
