{include file='header' pageTitle='wcf.acp.label.'|concat:$action}

<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Label.js?v={@LAST_UPDATE_TIME}"></script>
<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.Language.addObject({
			'wcf.acp.label.defaultValue': '{lang}wcf.acp.label.defaultValue{/lang}'
		});
		
		new WCF.Label.ACPList();
		
		$('#customCssClassName').click(function() {
			$(this).parents('li').find('input[type=radio]').click();
		});
	});
	//]]>
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.label.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='LabelList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.label.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

{if $labelGroupList|count}
	<form method="post" action="{if $action == 'add'}{link controller='LabelAdd'}{/link}{else}{link controller='LabelEdit' object=$label}{/link}{/if}">
		<div class="section">
			<dl{if $errorField == 'groupID'} class="formError"{/if}>
				<dt><label for="groupID">{lang}wcf.acp.label.group{/lang}</label></dt>
				<dd>
					<select id="groupID" name="groupID">
						<option value="0">{lang}wcf.global.noSelection{/lang}</option>
						{foreach from=$labelGroupList item=group}
							<option value="{@$group->groupID}"{if $group->groupID == $groupID} selected="selected"{/if}>{$group}{if $group->groupDescription} / {$group->groupDescription}{/if}</option>
						{/foreach}
					</select>
					{if $errorField == 'groupID'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.label.group.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'label'} class="formError"{/if}>
				<dt><label for="label">{lang}wcf.acp.label.label{/lang}</label></dt>
				<dd>
					<input type="text" id="label" name="label" value="{$i18nPlainValues['label']}" autofocus="autofocus" class="long" />
					{if $errorField == 'label'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{elseif $errorType == 'multilingual'}
								{lang}wcf.global.form.error.multilingual{/lang}
							{else}
								{lang}wcf.acp.label.label.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			{include file='multipleLanguageInputJavascript' elementIdentifier='label' forceSelection=false}
			
			<dl>
				<dt><label for="showOrder">{lang}wcf.acp.label.showOrder{/lang}</label></dt>
				<dd>
					<input type="number" min="0" id="showOrder" name="showOrder" class="tiny" value="{if $showOrder}{@$showOrder}{/if}" />
					<small>{lang}wcf.acp.label.showOrder.description{/lang}</small>
				</dd>
			</dl>
			
			<dl{if $errorField == 'cssClassName'} class="formError"{/if}>
				<dt><label for="cssClassName">{lang}wcf.acp.label.cssClassName{/lang}</label></dt>
				<dd>
					<ul id="labelList" class="inlineList">
						{foreach from=$availableCssClassNames item=className}
							{if $className == 'custom'}
								<li class="labelCustomClass"><input type="radio" name="cssClassName" value="custom"{if $cssClassName == 'custom'} checked="checked"{/if} /> <span><input type="text" id="customCssClassName" name="customCssClassName" value="{$customCssClassName}" class="long" /></span></li>
							{else}
								<li><label><input type="radio" name="cssClassName" value="{$className}"{if $cssClassName == $className} checked="checked"{/if} /> <span class="badge label{if $className != 'none'} {$className}{/if}">Label</span></label></li>
							{/if}
						{/foreach}
					</ul>
					
					{if $errorField == 'cssClassName'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.label.cssClassName.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			{event name='dataFields'}
		</div>
		
		{event name='sections'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</form>
{else}
	<p class="error">{lang}wcf.acp.label.error.noGroups{/lang}</p>
{/if}

{include file='footer'}
