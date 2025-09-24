{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}: {$queue->getTitle()}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$__wcf->getActivePage()->getTitle()}: {$queue->getTitle()}</h1>
			<ul class="inlineList contentHeaderMetaData">
				{event name='beforeMetaData'}

				{if $queue->lastChangeTime}
					<li title="{lang}wcf.moderation.lastChangeTime{/lang}">
						{icon name='clock'}
						{time time=$queue->lastChangeTime}
					</li>
				{/if}

				<li title="{lang}wcf.moderation.assignedUser{/lang}">
					{icon name='user'}
					<span id="moderationAssignedUser">
						{if $queue->assignedUserID}
							<a href="{link controller='User' id=$assignedUserID}{/link}" class="userLink" data-object-id="{$assignedUserID}">{$queue->assignedUsername}</a>
						{else}
							{lang}wcf.moderation.assignedUser.nobody{/lang}
						{/if}
					</span>
				</li>

				<li title="{lang}wcf.moderation.status{/lang}">
					{icon name='arrows-rotate'}
					<span id="moderationQueueStatus">{$queue->getStatus()}</span>
				</li>

				{event name='afterMetaData'}
			</ul>
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
			<button
				type="button"
				id="removeContent"
				class="contentInteractionButton button small jsOnly"
				data-object-id="{$queue->queueID}"
				data-object-name="{$queue->getTitle()}"
				data-redirect-url="{link controller='ModerationList'}filters[status]=0{/link}"
			>{icon name='xmark'} <span>{lang}wcf.moderation.activation.removeContent{/lang}</span></button>
		{/if}
		<button
			type="button"
			id="removeReport"
			class="contentInteractionButton button small jsOnly"
			data-object-id="{$queue->queueID}"
			data-redirect-url="{link controller='ModerationList'}filters[status]=0{/link}"
		>{icon name='square-check'} <span>{lang}wcf.moderation.report.removeReport{/lang}</span></button>
	{/if}
	{if $queue->canChangeJustifiedStatus()}
		<button
			type="button"
			id="changeJustifiedStatus"
			class="contentInteractionButton button small jsOnly"
			data-object-id="{$queue->queueID}"
			data-redirect-url="{link controller='ModerationReport' object=$queue}{/link}"
			data-justified="{if $queue->markAsJustified}true{else}false{/if}"
		>{icon name='arrows-rotate'} <span>{lang}wcf.moderation.report.changeJustifiedStatus{/lang}</span></button>
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
					<small class="separatorLeft">{time time=$queue->time}</small>
				</h3>
			</div>
			
			<div class="containerContent">{unsafe:$queue->getFormattedMessage()}</div>
		</div>
	</div>
</section>

<section class="section">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.moderation.report.reportedContent{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.type.{$queue->getObjectTypeName()}{/lang}</p>
	</header>
	
	{unsafe:$reportedContent}
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

	require(['WoltLabSuite/Core/Controller/Moderation/Report'], ({ setup }) => {
		{jsphrase name='wcf.moderation.report.removeReport.confirmMessage'}
		{jsphrase name='wcf.moderation.report.removeReport.markAsJustified'}
		{jsphrase name='wcf.moderation.report.changeJustifiedStatus.confirmMessage'}
		{jsphrase name='wcf.moderation.report.changeJustifiedStatus.markAsJustified'}
		
		setup(
			document.getElementById('removeContent'),
			document.getElementById('removeReport'),
			document.getElementById('changeJustifiedStatus')
		);
	});
</script>

{include file='footer'}
