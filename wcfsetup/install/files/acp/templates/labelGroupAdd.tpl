{include file='header' pageTitle='wcf.acp.label.group.'|concat:$action}

{include file='aclPermissions'}
<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Label.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Label.ACPList.Connect();
		
		WCF.TabMenu.init();
	});
	//]]>
</script>

{if !$groupID|isset}
	{include file='aclPermissionJavaScript' containerID='groupPermissions'}
{else}
	{include file='aclPermissionJavaScript' containerID='groupPermissions' objectID=$groupID}
{/if}

{assign var=labelForceSelection value=$forceSelection}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.group.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LabelGroupList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.label.group.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='LabelGroupAdd'}{/link}{else}{link controller='LabelGroupEdit' object=$labelGroup}{/link}{/if}">
	<div class="section tabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('general')}">{lang}wcf.global.form.data{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('connect')}">{lang}wcf.acp.label.group.category.connect{/lang}</a></li>
			</ul>
		</nav>
		
		<div id="general" class="tabMenuContent">
			<div class="section">
				<dl{if $errorField == 'groupName'} class="formError"{/if}>
					<dt><label for="groupName">{lang}wcf.global.title{/lang}</label></dt>
					<dd>
						<input type="text" id="groupName" name="groupName" value="{$i18nPlainValues['groupName']}" autofocus="autofocus" class="long" />
						{if $errorField == 'groupName'}
							<small class="innerError">
								{if $errorType == 'empty' || $errorType == 'multilingual'}
									{lang}wcf.global.form.error.{@$errorType}{/lang}
								{else}
									{lang}wcf.acp.label.group.groupName.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
						<small>{lang}wcf.acp.label.group.groupName.description{/lang}</small>
						{include file='multipleLanguageInputJavascript' elementIdentifier='groupName' forceSelection=false}
					</dd>
				</dl>
				
				<dl>
					<dt><label for="groupDescription">{lang}wcf.global.description{/lang}</label></dt>
					<dd>
						<input type="text" id="groupDescription" name="groupDescription" class="long" value="{$groupDescription}" maxlength="255" />
						<small>{lang}wcf.acp.label.group.groupDescription.description{/lang}</small>
					</dd>
				</dl>
				
				<dl>
					<dt><label for="showOrder">{lang}wcf.acp.label.group.showOrder{/lang}</label></dt>
					<dd>
						<input type="number" min="0" id="showOrder" name="showOrder" class="tiny" value="{if $showOrder}{@$showOrder}{/if}" />
					</dd>
				</dl>
				
				<dl>
					<dt></dt>
					<dd><label><input type="checkbox" name="forceSelection" id="forceSelection" value="1"{if $labelForceSelection} checked="checked"{/if} /> {lang}wcf.acp.label.group.forceSelection{/lang}</label></dd>
				</dl>
				
				<dl id="groupPermissions">
					<dt>{lang}wcf.acl.permissions{/lang}</dt>
					<dd></dd>
				</dl>
				
				{event name='dataFields'}
			</div>
			
			{event name='generalSections'}
		</div>
		
		<div id="connect" class="tabMenuContent">
			<div class="section">
				{foreach from=$labelObjectTypeContainers item=container}
					{if $container->isBooleanOption()}
						<!-- TODO: Implement boolean option mode -->
					{else}
						<dl>
							<dt>{lang}wcf.acp.label.container.{$container->getObjectTypeName()}{/lang}</dt>
							<dd>
								<ul class="structuredList">
									{foreach from=$container item=objectType}
										<li class="{if $objectType->isCategory()} category{/if}"{if $objectType->getDepth()} style="padding-left: {21 * $objectType->getDepth()}px"{/if} data-depth="{@$objectType->getDepth()}">
											<span>{$objectType->getLabel()}</span>
											<label><input id="checkbox_{@$container->getObjectTypeID()}_{@$objectType->getObjectID()}" type="checkbox" name="objectTypes[{@$container->getObjectTypeID()}][]" value="{@$objectType->getObjectID()}"{if $objectType->getOptionValue()} checked="checked"{/if} /></label>
										</li>
									{/foreach}
								</ul>
							</dd>
						</dl>
					{/if}
				{/foreach}
			</div>
			
			{event name='connectSections'}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
