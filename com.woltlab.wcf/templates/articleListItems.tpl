<div class="contentItemList">
	{foreach from=$objects item='article'}
		<article class="contentItem">
			<a href="{$article->getLink()}" class="contentItemLink">
				<div class="contentItemImage" style="background-image: url({if $article->getImage()}{$article->getImage()->getThumbnailLink('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoURL()}{/if})">
					{hascontent}
						<div class="contentItemBadges">
							{content}
								{if $article->isDeleted}<span class="badge label red contentItemBadge contentItemBadgeIsDeleted">{lang}wcf.message.status.deleted{/lang}</span>{/if}
								{if ARTICLE_ENABLE_VISIT_TRACKING && $article->isNew()}<span class="badge label contentItemBadge contentItemBadgeNew">{lang}wcf.message.new{/lang}</span>{/if}
							{/content}
						</div>
					{/hascontent}
				</div>
				
				<div class="contentItemContent">
					{if $article->hasLabels()}
						<div class="contentItemLabels">
							{foreach from=$article->getLabels() item=label}
								<span class="label badge contentItemLabel{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span>
							{/foreach}
						</div>
					{/if}
					
					<h2 class="contentItemTitle">{$article->getTitle()}</h2>
					
					<div class="contentItemDescription">
						{@$article->getFormattedTeaser()}
					</div>
				</div>
			</a>
			
			<div class="contentItemMeta">
				<a href="{$article->getUserProfile()->getLink()}" class="contentItemMetaImage" aria-hidden="true" tabindex="-1">
					{@$article->getUserProfile()->getAvatar()->getImageTag(32)}
				</a>
				
				<div class="contentItemMetaContent">
					<div class="contentItemMetaAuthor">
						{if $article->userID}
							<a href="{$article->getUserProfile()->getLink()}">{$article->getUserProfile()->username}</a>
						{else}
							{$article->username}
						{/if}
					</div>
					<div class="contentItemMetaTime">
						{@$article->time|time}
					</div>
				</div>
				
				<div class="contentItemMetaIcons">
					{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && ($article->likes || $article->dislikes || $article->neutralReactions)}
						<div class="contentItemMetaIcon reputationCounter {if $article->cumulativeLikes > 0}positive{elseif $article->cumulativeLikes < 0}negative{else}neutral{/if}">
							<span aria-label="{lang cumulativeLikes=$article->cumulativeLikes}wcf.like.reputation.label{/lang}">
								{if $article->cumulativeLikes > 0}+{elseif $article->cumulativeLikes == 0}±{/if}{#$article->cumulativeLikes}
							</span>
						</div>
					{/if}
					<div class="contentItemMetaIcon">
						<span class="icon icon16 fa-comments"></span>
						<span aria-label="{$article->getDiscussionProvider()->getDiscussionCountPhrase()}">
							{$article->getDiscussionProvider()->getDiscussionCount()}
						</span>
					</div>
				</div>
			</div>
		</article>
	{/foreach}
</div>
