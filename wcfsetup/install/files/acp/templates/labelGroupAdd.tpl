{include file='header'}

{include file='aclPermissions'}
<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Label{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
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

<header class="boxHeadline">
	<h1>{lang}wcf.acp.label.group.{$action}{/lang}</h1>
</header>

{if $errorField}
	<p class="error">{lang}wcf.global.form.error{/lang}</p>
{/if}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='LabelGroupList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.acp.menu.link.label.group.list{/lang}</span></a></li>
				
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='LabelGroupAdd'}{/link}{else}{link controller='LabelGroupEdit' object=$labelGroup}{/link}{/if}">
	<div class="tabMenuContainer">
		<nav class="tabMenu">
			<ul>
				<li><a href="{@$__wcf->getAnchor('general')}">{lang}wcf.global.form.data{/lang}</a></li>
				<li><a href="{@$__wcf->getAnchor('connect')}">{lang}wcf.acp.label.group.category.connect{/lang}</a></li>
			</ul>
		</nav>
		
		<div id="general" class="container containerPadding tabMenuContent">
			<fieldset>
				<legend>{lang}wcf.global.form.data{/lang}</legend>
				
				<dl{if $errorField == 'groupName'} class="formError"{/if}>
					<dt><label for="groupName">{lang}wcf.acp.label.group.groupName{/lang}</label></dt>
					<dd>
						<input type="text" id="groupName" name="groupName" value="{$groupName}" autofocus="autofocus" class="long" />
						{if $errorField == 'groupName'}
							<small class="innerError">
								{if $errorType == 'empty'}
									{lang}wcf.global.form.error.empty{/lang}
								{else}
									{lang}wcf.acp.label.group.groupName.error.{@$errorType}{/lang}
								{/if}
							</small>
						{/if}
					</dd>
				</dl>
				
				<dl>
					<dt class="reversed"><label for="forceSelection">{lang}wcf.acp.label.group.forceSelection{/lang}</label></dt>
					<dd><input type="checkbox" name="forceSelection" id="forceSelection" value="1"{if $forceSelection} checked="checked"{/if} /></dd>
				</dl>
				
				<dl id="groupPermissions">
					<dt>{lang}wcf.acl.permissions{/lang}</dt>
					<dd></dd>
				</dl>
				
				{event name='dataFields'}
			</fieldset>
			
			{event name='generalFieldsets'}
		</div>
		
		<div id="connect" class="container containerPadding tabMenuContent">
			<fieldset>
				<legend>{lang}wcf.acp.label.group.category.connect{/lang}</legend>
				
				{foreach from=$labelObjectTypeContainers item=container}
					{if $container->isBooleanOption()}
						<!-- TODO: Implement boolean option mode -->
					{else}
						<dl>
							<dt>{lang}wcf.acp.label.container.{$container->getObjectTypeName()}{/lang}</dt>
							<dd>
								<ul class="container structuredList">
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
			</fieldset>
			
			{event name='connectFieldsets'}
		</div>
	</div>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
	</div>
</form>

{include file='footer'}
