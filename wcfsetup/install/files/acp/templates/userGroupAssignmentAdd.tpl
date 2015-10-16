{include file='header' pageTitle='wcf.acp.group.assignment.'|concat:$action}

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		WCF.TabMenu.init();
	});
	//]]>
</script>

<header class="boxHeadline">
	<h1>{lang}wcf.acp.group.assignment.{$action}{/lang}</h1>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{@$action}{/lang}</p>
{/if}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='UserGroupAssignmentList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.group.assignment.button.list{/lang}</span></a></li>
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<form method="post" action="{if $action == 'add'}{link controller='UserGroupAssignmentAdd'}{/link}{else}{link controller='UserGroupAssignmentEdit' object=$assignment}{/link}{/if}">
	<div class="container containerPadding marginTop">
		<fieldset>
			<legend>{lang}wcf.global.form.data{/lang}</legend>
			
			<dl{if $errorField == 'title'} class="formError"{/if}>
				<dt><label for="title">{lang}wcf.global.name{/lang}</label></dt>
				<dd>
					<input type="text" id="title" name="title" value="{$title}" class="long" />
					{if $errorField == 'title'}
						<small class="innerError">
							{if $errorType == 'empty'}
								{lang}wcf.global.form.error.empty{/lang}
							{else}
								{lang}wcf.acp.group.assignment.title.error.{@$errorType}{/lang}
							{/if}
						</small>
					{/if}
				</dd>
			</dl>
			
			<dl{if $errorField == 'groupID'} class="formError"{/if}>
				<dt><label for="groupID">{lang}wcf.user.group{/lang}</label></dt>
				<dd>
					{htmlOptions name='groupID' options=$userGroups selected=$groupID}
					{if $errorField == 'groupID'}
						<small class="innerError">{lang}wcf.acp.group.assignment.groupID.error.{@$errorType}{/lang}</small>
					{/if}
				</dd>
			</dl>
			
			<dl class="formError">
				<dt></dt>
				<dd>
					<label><input type="checkbox" id="isDisabled" name="isDisabled"{if $isDisabled} checked="checked"{/if} /> {lang}wcf.acp.group.assignment.isDisabled{/lang}</label>
				</dd>
			</dl>
			
			{event name='dataFields'}
		</fieldset>
		
		{event name='fieldsets'}
	</div>
	
	<header class="boxHeadline boxSubHeadline">
		<h2>{lang}wcf.acp.group.assignment.conditions{/lang}</h2>
		<small>{lang}wcf.acp.group.assignment.conditions.description{/lang}</small>
	</header>
	
	{if $errorField == 'conditions'}
		<p class="error">{lang}wcf.acp.group.assignment.error.noConditions{/lang}</p>
	{/if}
	
	{include file='userConditions'}
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s" />
		<input type="hidden" name="action" value="{@$action}" />
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}
