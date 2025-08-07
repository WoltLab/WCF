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

<section class="section sectionContainerList">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.moderation.activation.content{/lang}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.type.{$queue->getObjectTypeName()}{/lang}</p>
	</header>

	{unsafe:$disabledContent}
</section>

<section id="comments" class="section sectionContainerList moderationComments">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $queue->comments} <span class="badge">{#$queue->comments}</span>{/if}</h2>
		<p class="sectionDescription">{lang}wcf.moderation.comments.description{/lang}</p>
	</header>
	
	{include file='comments' commentContainerID='moderationQueueCommentList' commentObjectID=$queueID}
</section>

{include file='footer'}
