<ul class="articleList">
	{foreach from=$objects item='article'}
		<li>
			<a href="{$article->getLink()}">
				{if $article->getTeaserImage() && $article->getTeaserImage()->hasThumbnail('tiny')}
					<div class="box128">
						<div class="articleListImage">{@$article->getTeaserImage()->getThumbnailTag('tiny')}</div>
				{/if}
					
					<div>
						<div class="containerHeadline">
							<h3 class="articleListTitle">{$article->getTitle()}</h3>
							<ul class="inlineList articleListMetaData">
								{if $article->hasLabels()}
									<li>
										<span class="icon icon16 fa-tags"></span>
										<ul class="labelList">
											{foreach from=$article->getLabels() item=label}
												<li><span class="label badge{if $label->getClassNames()} {$label->getClassNames()}{/if}">{lang}{$label->label}{/lang}</span></li>
											{/foreach}
										</ul>
									</li>
								{/if}
								
								<li>
									<span class="icon icon16 fa-clock-o"></span>
									{@$article->time|time}
								</li>
								
								{if $article->getDiscussionProvider()->getDiscussionCountPhrase()}
									<li>
										<span class="icon icon16 fa-comments"></span>
										{$article->getDiscussionProvider()->getDiscussionCountPhrase()}
									</li>
								{/if}
								
								{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike')}
									<li class="reputationCounter {if $article->cumulativeLikes > 0}positive{elseif $article->cumulativeLikes < 0}negative{else}neutral{/if}" data-object-id="{@$article->articleID}" data-object-type="com.woltlab.wcf.likeableArticle">
										{if $article->likes || $article->dislikes || $article->neutralReactions}
											<span class="icon icon16 fa-smile-o"></span>{if $article->cumulativeLikes > 0}+{elseif $article->cumulativeLikes == 0}±{/if}{#$article->cumulativeLikes}
										{/if}
									</li>
								{/if}
								
								{if ARTICLE_ENABLE_VISIT_TRACKING && $article->isNew()}<li><span class="badge label newMessageBadge">{lang}wcf.message.new{/lang}</span></li>{/if}

								{if $article->isDeleted}<li><span class="badge label red">{lang}wcf.message.status.deleted{/lang}</span></li>{/if}
								
								{event name='articleListMetaData'}
							</ul>
						</div>
						
						<div class="containerContent articleListTeaser">
							{@$article->getFormattedTeaser()}
						</div>
					</div>
						
				{if $article->getTeaserImage() && $article->getTeaserImage()->hasThumbnail('tiny')}
					</div>
				{/if}
				
				{event name='articleListEntry'}
			</a>
		</li>
	{/foreach}
</ul>
