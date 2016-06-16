<ul class="articleList">
	{foreach from=$objects item='article'}
		<li>
			<a href="{$article->getLink()}">
				{if $article->getImage()}
					<div class="box128">
						<div class="articleListImage">{@$article->getImage()->getThumbnailTag('tiny')}</div>
				{/if}
					
					<div>
						<div class="containerHeadline">
							<h3 class="articleListTitle">{$article->getTitle()}</h3>
							<ul class="inlineList articleListMetaData">
								<li>
									<span class="icon icon16 fa-clock-o"></span>
									{@$article->time|time}
								</li>
								
								<li>
									<span class="icon icon16 fa-comments"></span>
									{lang}wcf.article.articleComments{/lang}
								</li>
								
								{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike')}
									<li class="wcfLikeCounter{if $article->cumulativeLikes > 0} likeCounterLiked{elseif $article->cumulativeLikes < 0}likeCounterDisliked{/if}">
										{if $article->likes || $article->dislikes}
											<span class="icon icon16 fa-thumbs-o-{if $article->cumulativeLikes < 0}down{else}up{/if} jsTooltip" title="{lang likes=$article->likes dislikes=$article->dislikes}wcf.like.tooltip{/lang}"></span>{if $article->cumulativeLikes > 0}+{elseif $article->cumulativeLikes == 0}&plusmn;{/if}{#$article->cumulativeLikes}
										{/if}
									</li>
								{/if}
							</ul>
						</div>
						
						<div class="containerContent articleListTeaser">
							{@$article->getFormattedTeaser()}
						</div>
					</div>
					
				{if $article->getImage()}
					</div>
				{/if}
			</a>
		</li>
	{/foreach}
</ul>