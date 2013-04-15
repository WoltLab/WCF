{include file='header'}

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.ACP.Options.Group({if $canEditEveryone}true{else}false{/if});
	});
	//]]>
</script>

<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.group.option.editingOption{/lang} {lang}wcf.acp.group.option.{$userGroupOption->optionName}{/lang}</h1>
	</hgroup>
</header>

<p class="info marginTop">{lang}wcf.acp.group.option.hint{/lang}</p>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

<form method="post" action="{link controller='UserGroupOption'}{/link}" class="marginTop">
	<fieldset id="defaultValueContainer">
		<legend>{lang}wcf.acp.group.option.defaultValue{/lang}</legend>
		
		<dl data-group-id="{@$groupEveryone->groupID}">
			<dt><label for="optionValue{@$groupEveryone->groupID}">{lang}{$groupEveryone->groupName}{/lang}</label></dt>
			<dd>{@$defaultFormElement}</dd>
		</dl>
	</fieldset>
	
	<fieldset id="otherValueContainer">
		<legend>{lang}wcf.acp.group.option.other{/lang}</legend>
		
		{foreach from=$groups item=group}
			<dl data-group-id="{@$group->groupID}">
				<dt><label for="optionValue{@$group->groupID}">{lang}{$group->groupName}{/lang}</label></dt>
				<dd>{@$formElements[$group->groupID]}</dd>
			</dl>
		{/foreach}
	</fieldset>
	
	{event name='fieldsets'}
	
	<div class="formSubmit">
		<input type="button" value="{lang}wcf.global.button.submit{/lang}" id="submitButton" />
		<input type="hidden" name="id" value="{@$userGroupOption->optionID}" />
	</div>
</form>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{event name='contentNavigationButtonsBottom'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>

{include file='footer'}
