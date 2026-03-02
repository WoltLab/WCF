<ul class="sidebarList">
	{foreach from=$boxArticleList item=boxArticle}
		<li class="sidebarListItem">
			<div class="sidebarListItem__image">
				{unsafe:$boxArticle->getUserProfile()->getAvatar()->getImageTag(24)}
			</div>

			<div class="sidebarListItem__content">
				<h3 class="sidebarListItem__title">
					{anchor object=$boxArticle class='articleLink sidebarListItem__link' title=$boxArticle->getTitle()}
				</h3>
			</div>

			<div class="sidebarListItem__meta">
				{if $boxSortField === 'time'}
					<div class="sidebarListItem__meta__item sidebarListItem__meta__author">
						{unsafe:$boxArticle->getUserProfile()->getFormattedUsername()}
					</div>
					<div class="sidebarListItem__meta__item sidebarListItem__meta__time">
						{time time=$boxArticle->time}
					</div>
				{elseif $boxSortField === 'views'}
					<div class="sidebarListItem__meta__item sidebarListItem__meta__views">
						{lang article=$boxArticle}wcf.article.articleViews{/lang}
					</div>
				{elseif $boxSortField === 'comments'}
					<div class="sidebarListItem__meta__item sidebarListItem__meta__comments">
						{$boxArticle->getDiscussionProvider()->getDiscussionCountPhrase()}
					</div>
				{elseif $boxSortField === 'cumulativeLikes'}
					<div class="sidebarListItem__meta__item sidebarListItem__meta__reactions">
						{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike') && $boxArticle->cachedReactions}
							{include file='shared_topReaction' cachedReactions=$boxArticle->cachedReactions render='full'}
						{/if}
					</div>
				{/if}
			</div>
		</li>
	{/foreach}
</ul>
