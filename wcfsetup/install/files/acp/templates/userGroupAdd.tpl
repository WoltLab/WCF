{include file='header' pageTitle='wcf.acp.group.'|concat:$action}

<script data-relocate="true">
	$(function() {
		new WCF.Option.Handler();
		
		{if $action == 'edit' && $group->canCopy()}
			WCF.Language.addObject({
				'wcf.acp.group.copy.confirmMessage': '{jslang}wcf.acp.group.copy.confirmMessage{/jslang}',
				'wcf.acp.group.copy.copyACLOptions': '{jslang}wcf.acp.group.copy.copyACLOptions{/jslang}',
				'wcf.acp.group.copy.copyACLOptions.description': '{jslang}wcf.acp.group.copy.copyACLOptions.description{/jslang}',
				'wcf.acp.group.copy.copyMembers': '{jslang}wcf.acp.group.copy.copyMembers{/jslang}',
				'wcf.acp.group.copy.copyMembers.description': '{jslang}wcf.acp.group.copy.copyMembers.description{/jslang}',
				'wcf.acp.group.copy.copyUserGroupOptions': '{jslang}wcf.acp.group.copy.copyUserGroupOptions{/jslang}',
				'wcf.acp.group.copy.copyUserGroupOptions.description': '{jslang}wcf.acp.group.copy.copyUserGroupOptions.description{/jslang}'
			});
			
			new WCF.ACP.User.Group.Copy({@$groupID});
		{/if}
		
		{if $action === 'add' && $isBlankForm}
			elBySelAll('.jsBbcodeSelectOptionHtml input[type="checkbox"]', undefined, function (checkbox) {
				checkbox.checked = true;
			});
		{elseif $action == 'edit' && ($groupIsEveryone || $groupIsGuest || $groupIsUsers)}
			elBySelAll('.jsBbcodeSelectOptionHtml', undefined, function (bbcodeHtml) {
				elBySel('input[type="checkbox"]', bbcodeHtml).checked = true;
				
				elHide(bbcodeHtml);
			});
		{/if}
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.group.{@$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			{if $action == 'edit'}
				{if $availableUserGroups|count > 1}
					<li class="dropdown">
						<a class="button dropdownToggle">{icon name='sort'} <span>{lang}wcf.acp.group.button.choose{/lang}</span></a>
						<div class="dropdownMenu">
							<ul class="scrollableDropdownMenu">
								{foreach from=$availableUserGroups item='availableUserGroup'}
									<li{if $availableUserGroup->groupID == $groupID} class="active"{/if}><a href="{link controller='UserGroupEdit' id=$availableUserGroup->groupID}{/link}">{$availableUserGroup->getName()}</a></li>
								{/foreach}
							</ul>
						</div>
					</li>
				{/if}
				
				{if $group->canCopy()}
					<li><a class="jsButtonUserGroupCopy button">{icon name='copy'} <span>{lang}wcf.acp.group.button.copy{/lang}</span></a></li>
				{/if}
			{/if}
			
			<li><a href="{link controller='UserGroupList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.menu.link.group.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if VISITOR_USE_TINY_BUILD && $groupIsGuest}
	<woltlab-core-notice type="warning">{lang}wcf.acp.group.excludedInTinyBuild.notice{/lang}</woltlab-core-notice>
{/if}

{if $action == 'edit' && $group->isOwner()}
	<woltlab-core-notice type="info">{lang}wcf.acp.group.type.owner.description{/lang}</woltlab-core-notice>
{/if}

{if $warningSelfEdit|isset}
	<woltlab-core-notice type="warning">{lang}wcf.acp.group.edit.warning.selfIsMember{/lang}</woltlab-core-notice>
{/if}

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='UserGroupAdd'}{/link}{else}{link controller='UserGroupEdit' id=$groupID}{/link}{/if}">
	<div class="section">
		<dl{if $errorType.groupName|isset} class="formError"{/if}>
			<dt><label for="groupName">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="groupName" name="groupName" value="{$i18nPlainValues['groupName']}" autofocus maxlength="255" class="medium">
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

				{include file='shared_multipleLanguageInputJavascript' elementIdentifier='groupName' forceSelection=false}
			</dd>
		</dl>
		
		<dl{if $errorType.groupDescription|isset} class="formError"{/if}>
			<dt><label for="groupDescription">{lang}wcf.acp.group.description{/lang}</label></dt>
			<dd>
				<textarea id="groupDescription" name="groupDescription" cols="40" rows="10">{$i18nPlainValues['groupDescription']}</textarea>
				{if $errorType.groupDescription|isset}
					<small class="innerError">
						{lang}wcf.acp.group.description.error.{@$errorType.groupDescription}{/lang}
					</small>
				{/if}

				{include file='shared_multipleLanguageInputJavascript' elementIdentifier='groupDescription' forceSelection=false}
			</dd>
		</dl>
		
		<dl{if $errorType.priority|isset} class="formError"{/if}>
			<dt><label for="priority">{lang}wcf.acp.group.priority{/lang}</label></dt>
			<dd>
				<input type="number" id="priority" name="priority" value="{$priority}" class="tiny" max="8388607">
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
					<input type="text" id="userOnlineMarking" name="userOnlineMarking" value="{$userOnlineMarking}" maxlength="255" class="long">
					{if $errorType.userOnlineMarking|isset}
						<small class="innerError">
							{lang}wcf.acp.group.userOnlineMarking.error.{@$errorType.userOnlineMarking}{/lang}
						</small>
					{/if}
					<small>{lang}wcf.acp.group.userOnlineMarking.description{/lang}</small>
				</dd>
			</dl>
		{/if}
		
		{if $action == 'add' || $group->groupType > 3}
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="requireMultifactor" name="requireMultifactor" value="1"{if $requireMultifactor} checked{/if}> {lang}wcf.acp.group.requireMultifactor{/lang}</label>
					
					<small>{lang}wcf.acp.group.requireMultifactor.description{/lang}</small>
				</dd>
			</dl>
		{/if}
		
		{if MODULE_TEAM_PAGE && ($action == 'add' || $group->groupType > 3)}
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="showOnTeamPage" name="showOnTeamPage" value="1"{if $showOnTeamPage} checked{/if}> {lang}wcf.acp.group.showOnTeamPage{/lang}</label>
				</dd>
			</dl>
		{/if}

		{if $action === 'add' || !$isUnmentionableGroup}
			<dl>
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="allowMention" name="allowMention" value="1"{if $allowMention} checked{/if}> {lang}wcf.acp.group.allowMention{/lang}</label>
				</dd>
			</dl>
		{/if}
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<div class="section tabMenuContainer" data-active="{$activeTabMenuItem}" data-store="activeTabMenuItem">
		<nav class="tabMenu">
			<ul>
				{foreach from=$optionTree item=categoryLevel1}
					<li><a href="#{$categoryLevel1[object]->categoryName|rawurlencode}">{lang}wcf.acp.group.option.category.{@$categoryLevel1[object]->categoryName}{/lang}</a></li>
				{/foreach}
			</ul>
		</nav>
		
		{foreach from=$optionTree item=categoryLevel1}
			<div id="{@$categoryLevel1[object]->categoryName}" class="tabMenuContainer tabMenuContent">
				<nav class="menu">
					<ul>
						{foreach from=$categoryLevel1[categories] item=$categoryLevel2}
							{assign var=__categoryLevel2Name value=$categoryLevel1[object]->categoryName|concat:'-':$categoryLevel2[object]->categoryName}
							<li><a href="#{$__categoryLevel2Name|rawurlencode}">{lang}wcf.acp.group.option.category.{@$categoryLevel2[object]->categoryName}{/lang}</a></li>
						{/foreach}
					</ul>
				</nav>
				
				{foreach from=$categoryLevel1[categories] item=categoryLevel2}
					<div id="{@$categoryLevel1[object]->categoryName}-{@$categoryLevel2[object]->categoryName}" class="tabMenuContent hidden">
						{if $categoryLevel2[options]|count}
							<div class="section">
								{include file='optionFieldList' options=$categoryLevel2[options] langPrefix='wcf.acp.group.option.' isGuestGroup=$groupIsGuest}
							</div>
						{/if}
						
						{if $categoryLevel2[categories]|count}
							{foreach from=$categoryLevel2[categories] item=categoryLevel3}
								<section class="section">
									<header class="sectionHeader">
										<h2 class="sectionTitle">{lang}wcf.acp.group.option.category.{@$categoryLevel3[object]->categoryName}{/lang}</h2>
										{hascontent}<p class="sectionDescription">{content}{lang __optional=true}wcf.acp.group.option.category.{@$categoryLevel3[object]->categoryName}.description{/lang}{/content}</p>{/hascontent}
									</header>
										
									{include file='optionFieldList' options=$categoryLevel3[options] langPrefix='wcf.acp.group.option.' isGuestGroup=$groupIsGuest}
								</section>
							{/foreach}
						{/if}
					</div>
				{/foreach}
			</div>
		{/foreach}
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="action" value="{$action}">
		{csrfToken}
	</div>
</form>

{if $action === 'edit'}
	<script>
		(function () {
			{if $groupIsOwner}
				elBySelAll('input[name="values[admin.user.accessibleGroups][]"]', undefined, function(input) {
					var shadow = elCreate('input');
					shadow.type = 'hidden';
					shadow.name = input.name;
					shadow.value = input.value;
					
					input.parentNode.appendChild(shadow);
					
					input.disabled = true;
				});
				
				var permissions = [{implode from=$ownerGroupPermissions item=$_ownerPermission}'{$_ownerPermission|encodeJS}'{/implode}];
				permissions.forEach(function(permission) {
					elBySelAll('input[name="values[' + permission + ']"]', undefined, function (input) {
						if (input.value === '1') {
							input.checked = true;
						}
						else {
							input.disabled = true;
						}
					});
				});
			{elseif $ownerGroupID}
				var input = elBySel('input[name="values[admin.user.accessibleGroups][]"][value="{$ownerGroupID}"]');
				if (input) {
					elRemove(input.closest('label'));
				}
			{/if}
		})();
	</script>
{/if}

{include file='footer'}
