{include file='documentHeader'}

<head>
	<title>{lang}wcf.moderation.report{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
	<script data-relocate="true" src="{@$__wcf->getPath()}js/WCF.Moderation{if !ENABLE_DEBUG_MODE}.min{/if}.js?v={@$__wcfVersion}"></script>
	<script data-relocate="true">
		//<![CDATA[
		$(function() {
			WCF.Language.addObject({
				'wcf.moderation.report.removeContent.confirmMessage': '{lang}wcf.moderation.report.removeContent.confirmMessage{/lang}',
				'wcf.moderation.report.removeContent.reason': '{lang}wcf.moderation.report.removeContent.reason{/lang}',
				'wcf.moderation.report.removeReport.confirmMessage': '{lang}wcf.moderation.report.removeReport.confirmMessage{/lang}'
			});
			
			new WCF.Moderation.Report.Management({@$queue->queueID}, '{link controller='ModerationList'}{/link}');
		});
		//]]>
	</script>
</head>

<body id="tpl{$templateName|ucfirst}">

{include file='header' sidebarOrientation='left'}

<header class="boxHeadline">
	<h1>{lang}wcf.moderation.report{/lang}</h1>
</header>

{include file='userNotice'}

{include file='formError'}

<div class="contentNavigation">
	<nav>
		<ul>
			<li><a href="{link controller='ModerationList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.moderation.moderation{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

<form method="post" action="{link controller='ModerationReport' id=$queue->queueID}{/link}" class="container containerPadding marginTop">
	<fieldset>
		<legend>{lang}wcf.moderation.report.details{/lang}</legend>
		
		<dl>
			<dt>{lang}wcf.global.objectID{/lang}</dt>
			<dd>{#$queue->queueID}</dd>
		</dl>
		<dl>
			<dt>{lang}wcf.moderation.report.reportedBy{/lang}</dt>
			<dd>{if $queue->userID}<a href="{link controller='User' id=$queue->userID}{/link}" class="userLink" data-user-id="{@$queue->userID}">{$queue->username}</a>{else}{lang}wcf.user.guest{/lang}{/if} ({@$queue->time|time})</dd>
		</dl>
		{if $queue->lastChangeTime}
			<dl>
				<dt>{lang}wcf.moderation.lastChangeTime{/lang}</dt>
				<dd>{@$queue->lastChangeTime|time}</dd>
			</dl>
		{/if}
		<dl>
			<dt>{lang}wcf.moderation.assignedUser{/lang}</dt>
			<dd>
				<ul>
					{if $assignedUserID && ($assignedUserID != $__wcf->getUser()->userID)}
						<li><label><input type="radio" name="assignedUserID" value="{@$assignedUserID}" checked="checked" /> {$queue->assignedUsername}</label></li>
					{/if}
					<li><label><input type="radio" name="assignedUserID" value="{@$__wcf->getUser()->userID}"{if $assignedUserID == $__wcf->getUser()->userID} checked="checked"{/if} /> {$__wcf->getUser()->username}</label></li>
					<li><label><input type="radio" name="assignedUserID" value="0"{if !$assignedUserID} checked="checked"{/if} /> {lang}wcf.moderation.assignedUser.nobody{/lang}</label></li>
				</ul>
			</dd>
		</dl>
		{if $queue->assignedUser}
			<dl>
				<dt></dt>
				<dd><a href="{link controller='User' id=$assignedUserID}{/link}" class="userLink" data-user-id="{@$assignedUserID}">{$queue->assignedUsername}</a></dd>
			</dl>
		{/if}
		<dl>
			<dt>{lang}wcf.moderation.report.reason{/lang}</dt>
			<dd>{@$queue->getFormattedMessage()}</dd>
		</dl>
		<dl>
			<dt><label for="comment">{lang}wcf.moderation.comment{/lang}</label></dt>
			<dd><textarea id="comment" name="comment" rows="4" cols="40">{$comment}</textarea></dd>
		</dl>
		
		{event name='detailsFields'}
		
		<div class="formSubmit">
			<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
			{@SECURITY_TOKEN_INPUT_TAG}
		</div>
	</fieldset>
	
	{event name='fieldsets'}
</form>

<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.moderation.report.reportedContent{/lang}</h2>
</header>

<div class="marginTop">
	{@$reportedContent}
</div>

<div class="contentNavigation">
	<nav>
		<ul>
			{if !$queue->isDone()}
				<li class="jsOnly"><a id="removeContent" class="button"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.moderation.report.removeContent{/lang}</span></a></li>
				<li class="jsOnly"><a id="removeReport" class="button"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.moderation.report.removeReport{/lang}</span></a></li>
			{/if}
			<li><a href="{link controller='ModerationList'}{/link}" class="button"><span class="icon icon16 icon-list"></span> <span>{lang}wcf.moderation.moderation{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}

</body>
</html>