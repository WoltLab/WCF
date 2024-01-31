{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}: {$queue->getTitle()}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}</h1>
			
			{if $queue->lastChangeTime}
				<dl class="plain inlineDataList">
					<dt>{lang}wcf.moderation.lastChangeTime{/lang}</dt>
					<dd>{@$queue->lastChangeTime|time}</dd>
				</dl>
			{/if}
			
			<dl class="plain inlineDataList" id="moderationAssignedUserContainer">
				<dt>{lang}wcf.moderation.assignedUser{/lang}</dt>
				<dd id="moderationAssignedUser">
					{if $queue->assignedUserID}
						<a href="{link controller='User' id=$assignedUserID}{/link}" class="userLink" data-object-id="{@$assignedUserID}">{$queue->assignedUsername}</a>
					{else}
						{lang}wcf.moderation.assignedUser.nobody{/lang}
					{/if}
				</dd>
			</dl>
			
			<dl class="plain inlineDataList" id="moderationStatusContainer">
				<dt>{lang}wcf.moderation.status{/lang}</dt>
				<dd id="moderationQueueStatus">{$queue->getStatus()}</dd>
			</dl>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						{if $queue->getAffectedObject()}<li><a href="{$queue->getAffectedObject()->getLink()}" class="button buttonPrimary">{icon name='arrow-right'} <span>{lang}wcf.moderation.jumpToContent{/lang}</span></a></li>{/if}
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{capture assign='contentInteractionButtons'}
	<button
		type="button"
		id="moderationAssignUser"
		class="contentInteractionButton button small jsOnly"
		data-url="{$queue->endpointAssignUser()}"
	>
		{icon name='user-plus' type='solid'}
		<span>{lang}wcf.moderation.assignedUser.change{/lang}</span>
	</button>
	{if !$queue->isDone()}
		{if $queueManager->canRemoveContent($queue->getDecoratedObject())}
			<button type="button" id="removeContent" class="contentInteractionButton button small jsOnly">{icon name='xmark'} <span>{lang}wcf.moderation.activation.removeContent{/lang}</span></button>
		{/if}
		<button type="button" id="removeReport" class="contentInteractionButton button small jsOnly">{icon name='square-check'} <span>{lang}wcf.moderation.report.removeReport{/lang}</span></button>
	{/if}
	{if $queue->canChangeJustifiedStatus()}
		<button type="button" id="changeJustifiedStatus" class="contentInteractionButton button small jsOnly">{icon name='arrows-rotate'} <span>{lang}wcf.moderation.report.changeJustifiedStatus{/lang}</span></button>
	{/if}
{/capture}

{include file='header'}

{include file='shared_formError'}

<section class="section">
	<h2 class="sectionTitle">{lang}wcf.moderation.report.reportedBy{/lang}</h2>
	
	<div class="box32">
		{user object=$reportUser type='avatar32' ariaHidden='true' tabindex='-1'}
		
		<div>
			<div class="containerHeadline">
				<h3>
					{if $reportUser->userID}
						{user object=$reportUser}
					{else}
						{lang}wcf.user.guest{/lang}
					{/if}
					<small class="separatorLeft">{@$queue->time|time}</small>
				</h3>
			</div>
			
			<div class="containerContent">{@$queue->getFormattedMessage()}</div>
		</div>
	</div>
</section>

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.moderation.report.reportedContent{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.type.{@$queue->getObjectTypeName()}{/lang}</p>
	</header>
	
	{@$reportedContent}
</section>

<section id="comments" class="section sectionContainerList moderationComments">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $queue->comments} <span class="badge">{#$queue->comments}</span>{/if}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.comments.description{/lang}</p>
	</header>
	
	{include file='comments' commentContainerID='moderationQueueCommentList' commentObjectID=$queueID}
</section>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Controller/Moderation/AssignUser'], ({ setup }) => {
		{jsphrase name='wcf.moderation.assignedUser.nobody'}
		
		setup(document.getElementById('moderationAssignUser'));
	});

	$(function() {
		WCF.Language.addObject({
			'wcf.moderation.report.removeContent.confirmMessage': '{jslang}wcf.moderation.report.removeContent.confirmMessage{/jslang}',
			'wcf.moderation.report.removeContent.reason': '{jslang}wcf.moderation.report.removeContent.reason{/jslang}',
			'wcf.moderation.report.removeReport.confirmMessage': '{jslang}wcf.moderation.report.removeReport.confirmMessage{/jslang}',
			'wcf.moderation.report.removeReport.markAsJustified': '{jslang}wcf.moderation.report.removeReport.markAsJustified{/jslang}',
			'wcf.moderation.report.removeReport.confirmMessage': '{jslang}wcf.moderation.report.removeReport.confirmMessage{/jslang}',
			'wcf.moderation.report.changeJustifiedStatus.markAsJustified': '{jslang}wcf.moderation.report.changeJustifiedStatus.markAsJustified{/jslang}',
			'wcf.moderation.report.changeJustifiedStatus.confirmMessage': '{jslang}wcf.moderation.report.changeJustifiedStatus.confirmMessage{/jslang}',
		});
		
		new WCF.Moderation.Report.Management(
			{@$queue->queueID},
			'{link controller='ModerationList' encode=false}{/link}',
			{if $queue->markAsJustified}true{else}false{/if}
		);
	});
</script>

{include file='footer'}
