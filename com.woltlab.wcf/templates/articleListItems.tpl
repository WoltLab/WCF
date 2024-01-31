{if !$disableAds|isset}{assign var='disableAds' value=false}{/if}

<div class="contentItemList">
	{foreach from=$objects item='article' name='articles'}
		{if $article->getArticleContent()}
		<article class="contentItem contentItemMultiColumn">
			<div class="contentItemLink">
				<div class="contentItemImage contentItemImageLarge">
					<img
						class="contentItemImageElement"
						src="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailLink('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoURL()}{/if}"
						height="{if $article->getTeaserImage()}{@$article->getTeaserImage()->getThumbnailHeight('medium')}{else}{@$__wcf->getStyleHandler()->getStyle()->getCoverPhotoHeight()}{/if}"
						width="{if $article->getTeaserImage()}{@$article->getTeaserImage()->getThumbnailWidth('medium')}{else}{@$__wcf->getStyleHandler()->getStyle()->getCoverPhotoWidth()}{/if}"
						loading="lazy"
						alt="">
					
					{hascontent}
						<div class="contentItemBadges">
							{content}
								{if $article->isDeleted}<span class="badge label red contentItemBadge contentItemBadgeIsDeleted">{lang}wcf.message.status.deleted{/lang}</span>{/if}
								{if !$article->isPublished()}<span class="badge label green contentItemBadge contentItemBadgeIsDisabled">{lang}wcf.message.status.disabled{/lang}</span>{/if}
								{if $article->isNew()}<span class="badge label contentItemBadge contentItemBadgeNew">{lang}wcf.message.new{/lang}</span>{/if}
								
								{event name='contentItemBadges'}
							{/content}
						</div>
					{/hascontent}
				</div>
				
				<div class="contentItemContent">
					{if $article->hasLabels()}
						<div class="contentItemLabels">
							{foreach from=$article->getLabels() item=label}
								{@$label->render('contentItemLabel')}
							{/foreach}
						</div>
					{/if}
					
					<h2 class="contentItemTitle"><a href="{$article->getLink()}" class="contentItemTitleLink">{$article->getTitle()}</a></h2>
					
					<div class="contentItemDescription">
						{@$article->getFormattedTeaser()}
					</div>
				</div>
			</div>
			
			<div class="contentItemMeta">
				<span class="contentItemMetaImage">
					{@$article->getUserProfile()->getAvatar()->getImageTag(32)}
				</span>
				
				<div class="contentItemMetaContent">
					<div class="contentItemMetaAuthor">
						{@$article->getUserProfile()->getFormattedUsername()}
					</div>
					<div class="contentItemMetaTime">
						{@$article->time|time}
					</div>
				</div>
				
				<div class="contentItemMetaIcons">
					{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $article->cumulativeLikes}
						<div class="contentItemMetaIcon">
							{include file='shared_topReaction' cachedReactions=$article->cachedReactions render='short'}
						</div>
					{/if}
					{if $article->getDiscussionProvider()->getDiscussionCountPhrase()}{* empty phrase indicates that comments are disabled *}
						<div class="contentItemMetaIcon">
							{icon name='comments'}
							<span aria-label="{$article->getDiscussionProvider()->getDiscussionCountPhrase()}">
								{$article->getDiscussionProvider()->getDiscussionCount()}
							</span>
						</div>
					{/if}

					{event name='contentItemMetaIcons'}
				</div>
			</div>
		</article>
		{/if}
		
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
