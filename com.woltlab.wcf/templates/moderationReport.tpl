{include file='documentHeader'}

<head>
	<title>{lang}wcf.moderation.report{/lang}: {$queue->getTitle()} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude'}
	
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

{capture assign='sidebar'}
	<form method="post" action="{link controller='ModerationReport' id=$queue->queueID}{/link}">
		<fieldset>
			<legend>{lang}wcf.moderation.report.details{/lang}</legend>
			
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
			
			{event name='detailsFields'}
			
			<div class="formSubmit">
				<input type="submit" value="{lang}wcf.global.button.submit{/lang}" />
				{@SECURITY_TOKEN_INPUT_TAG}
			</div>
		</fieldset>
		
		{event name='fieldsets'}
	</form>
	
	{event name='boxes'}
{/capture}

{include file='header' sidebarOrientation='right'}

<header class="boxHeadline">
	<h1>{lang}wcf.moderation.report{/lang}: {$queue->getTitle()}</h1>
	
	{if $queue->lastChangeTime}
		<dl class="plain inlineDataList">
			<dt>{lang}wcf.moderation.lastChangeTime{/lang}</dt>
			<dd>{@$queue->lastChangeTime|time}</dd>
		</dl>
	{/if}
</header>

{include file='userNotice'}

{include file='formError'}

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
				{if $queueManager->canRemoveContent($queue->getDecoratedObject())}<li class="jsOnly"><a id="removeContent" class="button"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.moderation.report.removeContent{/lang}</span></a></li>{/if}
				<li class="jsOnly"><a id="removeReport" class="button"><span class="icon icon16 icon-remove"></span> <span>{lang}wcf.moderation.report.removeReport{/lang}</span></a></li>
			{/if}
			{if $queue->getAffectedObject()}<li><a href="{$queue->getAffectedObject()->getLink()}" class="button"><span class="icon icon16 fa-arrow-right"></span> <span>{lang}wcf.moderation.jumpToContent{/lang}</span></a></li>{/if}
			
			{event name='contentNavigationButtons'}
		</ul>
	</nav>
</div>

<header class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.moderation.report.reportedBy{/lang}</h2>
</header>

<div class="container containerPadding marginTop">
	<div class="box32">
		{if $reportUser->userID}
			<a href="{link controller='User' object=$reportUser}{/link}" title="{$reportUser->username}" class="framed">
				{@$reportUser->getAvatar()->getImageTag(32)}
			</a>
		{else}
			<span class="framed">{@$reportUser->getAvatar()->getImageTag(32)}</span>
		{/if}
		
		<div>
			<div class="containerHeadline">
				<h3>
					{if $queue->userID}
						<a href="{link controller='User' id=$queue->userID}{/link}" class="userLink" data-user-id="{@$queue->userID}">{$queue->username}</a>
					{else}
						{lang}wcf.user.guest{/lang}
					{/if}
					
					<small> - {@$queue->time|time}</small>
				</h3>
			</div>
			
			<div>{@$queue->getFormattedMessage()}</div>
		</div>
	</div>
</div>

<header id="comments" class="boxHeadline boxSubHeadline">
	<h2>{lang}wcf.moderation.comments{/lang} <span class="badge">{#$queue->comments}</span></h2>
	<p>{lang}wcf.moderation.comments.description{/lang}</p>
</header>

{include file='__commentJavaScript' commentContainerID='moderationQueueCommentList'}

<div class="container containerList marginTop blogEntryComments">
	<ul id="moderationQueueCommentList" class="commentList containerList" data-can-add="true" data-object-id="{@$queueID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{@$commentList->countObjects()}" data-last-comment-time="{@$lastCommentTime}">
		{include file='commentList'}
	</ul>
</div>

{include file='footer'}

</body>
</html>