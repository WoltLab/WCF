{if !$disableAds|isset}{assign var='disableAds' value=false}{/if}

<div class="contentItemList">
	{foreach from=$objects item='article' name='articles'}
		<article class="contentItem contentItemMultiColumn" role="article">
			<div class="contentItemLink">
				<div class="contentItemImage contentItemImageLarge" style="background-image: url({if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailLink('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoURL()}{/if})">
					{hascontent}
						<div class="contentItemBadges">
							{content}
								{if $article->isDeleted}<span class="badge label red contentItemBadge contentItemBadgeIsDeleted">{lang}wcf.message.status.deleted{/lang}</span>{/if}
								{if !$article->isPublished()}<span class="badge label green contentItemBadge contentItemBadgeIsDisabled">{lang}wcf.message.status.disabled{/lang}</span>{/if}
								{if ARTICLE_ENABLE_VISIT_TRACKING && $article->isNew()}<span class="badge label contentItemBadge contentItemBadgeNew">{lang}wcf.message.new{/lang}</span>{/if}
								
								{event name='contentItemBadges'}
							{/content}
						</div>
					{/hascontent}
				</div>
				
				<div class="contentItemContent">
					{if $article->hasLabels()}
						<div class="contentItemLabels">
							{foreach from=$article->getLabels() item=label}
								<span class="label badge contentItemLabel{if $label->getClassNames()} {$label->getClassNames()}{/if}">{$label->getTitle()}</span>
							{/foreach}
						</div>
					{/if}
					
					<h2 class="contentItemTitle">{$article->getTitle()}</h2>
					
					<div class="contentItemDescription">
						{@$article->getFormattedTeaser()}
					</div>
				</div>
				
				<a href="{$article->getLink()}" class="contentItemLinkShadow"></a>
			</div>
			
			<div class="contentItemMeta">
				<a href="{$article->getUserProfile()->getLink()}" class="contentItemMetaImage" aria-hidden="true" tabindex="-1">
					{@$article->getUserProfile()->getAvatar()->getImageTag(32)}
				</a>
				
				<div class="contentItemMetaContent">
					<div class="contentItemMetaAuthor">
						{if $article->userID}
							<a href="{$article->getUserProfile()->getLink()}" class="userLink" data-user-id="{@$article->userID}">{$article->getUserProfile()->username}</a>
						{else}
							{$article->username}
						{/if}
					</div>
					<div class="contentItemMetaTime">
						{@$article->time|time}
					</div>
				</div>
				
				<div class="contentItemMetaIcons">
					{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $article->cumulativeLikes}
						<div class="contentItemMetaIcon">
							{include file='__topReaction' cachedReactions=$article->cachedReactions render='short'}
						</div>
					{/if}
					<div class="contentItemMetaIcon">
						<span class="icon icon16 fa-comments"></span>
						<span aria-label="{$article->getDiscussionProvider()->getDiscussionCountPhrase()}">
							{$article->getDiscussionProvider()->getDiscussionCount()}
						</span>
					</div>
					
					{event name='contentItemMetaIcons'}
				</div>
			</div>
		</article>
		
		{if MODULE_WCF_AD && !$disableAds}
			{if $tpl[foreach][articles][iteration] === 1}
				{hascontent}
					<div class="contentItem contentItemAd">
						{content}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.after1stArticle')}{/content}
					</div>
				{/hascontent}
			{else}
				{if $tpl[foreach][articles][iteration] % 2 === 0}
					{hascontent}
						<div class="contentItem contentItemAd">
							{content}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery2ndArticle')}{/content}
						</div>
					{/hascontent}
				{/if}
				
				{if $tpl[foreach][articles][iteration] % 3 === 0}
					{hascontent}
						<div class="contentItem contentItemAd">
							{content}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery3rdArticle')}{/content}
						</div>
					{/hascontent}
				{/if}
				
				{if $tpl[foreach][articles][iteration] % 5 === 0}
					{hascontent}
						<div class="contentItem contentItemAd">
							{content}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery5thArticle')}{/content}
						</div>
					{/hascontent}
					
					{if $tpl[foreach][articles][iteration] % 10 === 0}
						{hascontent}
							<div class="contentItem contentItemAd">
								{content}{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery10thArticle')}{/content}
							</div>
						{/hascontent}
					{/if}
				{/if}
			{/if}
		{/if}
	{/foreach}
</div>
