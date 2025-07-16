{if !$disableAds|isset}{assign var='disableAds' value=false}{/if}

{foreach from=$view->getItems() item='article' name='articles'}
	{if $article->getArticleContent()}
	<article class="entryCardList__item listView__item" data-object-id="{$article->getObjectID()}">
		<div class="entryCardList__item__buttons">
			{if $view->hasBulkInteractions()}
				<label class="listView__selectItem__label jsTooltip" title="{lang}wcf.clipboard.item.mark{/lang}">
					<input type="checkbox" class="listView__selectItem" aria-label="{lang}wcf.clipboard.item.mark{/lang}">
				</label>
			{/if}

			{unsafe:$view->renderInteractionContextMenuButton($article)}
		</div>

		<div class="entryCardList__item__image">
			<img
				class="entryCardList__item__image__element"
				src="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailLink('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoURL()}{/if}"
				height="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailHeight('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoHeight()}{/if}"
				width="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailWidth('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoWidth()}{/if}"
				loading="lazy"
				alt=""
			>

			{hascontent}
				<div class="entryCardList__item__badges">
					{content}
						{if $article->isDeleted}<span class="badge red">{lang}wcf.message.status.deleted{/lang}</span>{/if}
						{if !$article->isPublished()}<span class="badge green">{lang}wcf.message.status.disabled{/lang}</span>{/if}
						{if $article->isNew()}<span class="badge">{lang}wcf.message.new{/lang}</span>{/if}
						
						{event name='contentItemBadges'}{* deprecated: use badges instead *}
						{event name='badges'}
					{/content}
				</div>
			{/hascontent}
		</div>

		<div class="entryCardList__item__content">
			{if $article->hasLabels()}
				<ul class="entryCardList__item__labels labelList">
					{foreach from=$article->getLabels() item=label}
						<li>{unsafe:$label->render()}</li>
					{/foreach}
				</ul>
			{/if}
			
			<h2 class="entryCardList__item__title">
				<a href="{$article->getLink()}" class="entryCardList__item__link">{$article->getTitle()}</a>
			</h2>

			<div class="entryCardList__item__teaser">
				{unsafe:$article->getFormattedTeaser()}
			</div>
		</div>

		<div class="entryCardList__item__meta">
			<div class="entryCardList__item__meta__image">
				{unsafe:$article->getUserProfile()->getAvatar()->getImageTag(32)}
			</div>
			
			<div class="entryCardList__item__meta__content">
				<div class="entryCardList__item__meta__author">
					{unsafe:$article->getUserProfile()->getFormattedUsername()}
				</div>
				
				<div class="entryCardList__item__meta__time">
					{time time=$article->time}
				</div>
			</div>

			<div class="entryCardList__item__meta__icons">
				{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $article->cumulativeLikes}
					<div class="entryCardList__item__meta__icon">
						{include file='shared_topReaction' cachedReactions=$article->cachedReactions render='short'}
					</div>
				{/if}
				{if $article->getDiscussionProvider()->getDiscussionCountPhrase()}{* empty phrase indicates that comments are disabled *}
					<div class="entryCardList__item__meta__icon">
						{icon name='comments'}
						<span aria-label="{$article->getDiscussionProvider()->getDiscussionCountPhrase()}">
							{$article->getDiscussionProvider()->getDiscussionCount()}
						</span>
					</div>
				{/if}

				{event name='contentItemMetaIcons'}{* deprecated: use badges instead *}
				{event name='metaIcons'}
			</div>
		</div>
	</article>
	{/if}
	
	{if MODULE_WCF_AD && !$disableAds}
		{if $tpl[foreach][articles][iteration] === 1}
			{hascontent}
				<div class="entryCardList__item entryCardList__item--ad">
					{content}{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.after1stArticle')}{/content}
				</div>
			{/hascontent}
		{else}
			{if $tpl[foreach][articles][iteration] % 2 === 0}
				{hascontent}
					<div class="entryCardList__item entryCardList__item--ad">
						{content}{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery2ndArticle')}{/content}
					</div>
				{/hascontent}
			{/if}
			
			{if $tpl[foreach][articles][iteration] % 3 === 0}
				{hascontent}
					<div class="entryCardList__item entryCardList__item--ad">
						{content}{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery3rdArticle')}{/content}
					</div>
				{/hascontent}
			{/if}
			
			{if $tpl[foreach][articles][iteration] % 5 === 0}
				{hascontent}
					<div class="entryCardList__item entryCardList__item--ad">
						{content}{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery5thArticle')}{/content}
					</div>
				{/hascontent}
				
				{if $tpl[foreach][articles][iteration] % 10 === 0}
					{hascontent}
						<div class="entryCardList__item entryCardList__item--ad">
							{content}{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.afterEvery10thArticle')}{/content}
						</div>
					{/hascontent}
				{/if}
			{/if}
		{/if}
	{/if}
{/foreach}
