{capture assign='pageTitle'}{$__wcf->getActivePage()->getTitle()}: {$queue->getTitle()}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader">
		{include file='moderationContentHeader' title=$__wcf->getActivePage()->getTitle() queue=$queue sandbox=true}
		
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
	<div class="contentInteractionButton">
		{unsafe:$interactionContextMenu->render()}
	</div>
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

{include file='footer'}
