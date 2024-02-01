{include file='header' pageTitle='wcf.acp.group.assignment.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.group.assignment.{$action}{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='UserGroupAssignmentList'}{/link}" class="button">{icon name='list'} <span>{lang}wcf.acp.group.assignment.button.list{/lang}</span></a></li>
			
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='shared_formNotice'}

<form method="post" action="{if $action == 'add'}{link controller='UserGroupAssignmentAdd'}{/link}{else}{link controller='UserGroupAssignmentEdit' object=$assignment}{/link}{/if}">
	<div class="section">
		<dl{if $errorField == 'title'} class="formError"{/if}>
			<dt><label for="title">{lang}wcf.global.name{/lang}</label></dt>
			<dd>
				<input type="text" id="title" name="title" value="{$title}" class="long">
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
				{htmlOptions name='groupID' id='groupID' options=$userGroups selected=$groupID}
				{if $errorField == 'groupID'}
					{if $errorType == 'noValidSelection'}
						<small class="innerError">{lang}wcf.global.form.error.noValidSelection{/lang}</small>
					{else}
						<small class="innerError">{lang}wcf.acp.group.assignment.groupID.error.{@$errorType}{/lang}</small>
					{/if}
				{/if}
			</dd>
		</dl>
		
		<dl>
			<dt></dt>
			<dd>
				<label><input type="checkbox" id="isDisabled" name="isDisabled"{if $isDisabled} checked{/if}> {lang}wcf.acp.group.assignment.isDisabled{/lang}</label>
			</dd>
		</dl>
		
		{event name='dataFields'}
	</div>
	
	{event name='sections'}
	
	<section class="section">
		<header class="sectionHeader">
			<h2 class="sectionTitle">{lang}wcf.acp.group.assignment.conditions{/lang}</h2>
			<p class="sectionDescription">{lang}wcf.acp.group.assignment.conditions.description{/lang}</p>
		</header>
		
		{if $errorField == 'conditions'}
			<woltlab-core-notice type="error">{lang}wcf.acp.group.assignment.error.noConditions{/lang}</woltlab-core-notice>
		{/if}

		{include file='shared_userConditions'}
	</section>
	
	<div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		<input type="hidden" name="action" value="{$action}">
		{csrfToken}
	</div>
</form>

{include file='footer'}
