{capture assign='pageTitle'}{$articleContent->title}{/capture}

{assign var='__mainItemScope' value='itemprop="mainEntity" itemscope itemtype="http://schema.org/Article"'}

{capture assign='contentHeader'}
	<header class="contentHeader articleContentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle" itemprop="name headline">{$articleContent->title}</h1>
			<ul class="inlineList contentHeaderMetaData articleMetaData">
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
				
				<li itemprop="author" itemscope itemtype="http://schema.org/Person">
					<span class="icon icon16 fa-user"></span>
					{if $article->userID}
						<a href="{link controller='User' id=$article->userID title=$article->username}{/link}" class="userLink" data-user-id="{@$article->userID}" itemprop="url">
							<span itemprop="name">{$article->username}</span>
						</a>
					{else}
						<span itemprop="name">{$article->username}</span>
					{/if}
				</li>
				
				<li>
					<span class="icon icon16 fa-clock-o"></span>
					<a href="{$article->getLink()}">{@$article->time|time}</a>
					<meta itemprop="datePublished" content="{@$article->time|date:'c'}">
					<meta itemprop="dateModified" content="{@$article->time|date:'c'}">
				</li>
				
				{if $article->enableComments}
					<li itemprop="interactionStatistic" itemscope itemtype="http://schema.org/InteractionCounter">
						<span class="icon icon16 fa-comments"></span>
						<span>{lang}wcf.article.articleComments{/lang}</span>
						<meta itemprop="interactionType" content="http://schema.org/CommentAction">
						<meta itemprop="userInteractionCount" content="{@$article->comments}">
					</li>
				{/if}
				
				<li>
					<span class="icon icon16 fa-eye"></span>
					{lang}wcf.article.articleViews{/lang}
				</li>
				
				{if ARTICLE_ENABLE_VISIT_TRACKING && $article->isNew()}<li><span class="badge label newMessageBadge">{lang}wcf.message.new{/lang}</span></li>{/if}
				
				{if $article->isDeleted}<li><span class="badge label red">{lang}wcf.message.status.deleted{/lang}</span></li>{/if}
				
				<li class="articleLikesBadge"></li>
				
				{event name='contentHeaderMetaData'}
			</ul>
			
			<meta itemprop="mainEntityOfPage" content="{$canonicalURL}">
			<div itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
				<meta itemprop="name" content="{PAGE_TITLE|language}">
				<div itemprop="logo" itemscope itemtype="http://schema.org/ImageObject">
					<meta itemprop="url" content="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}">
				</div>
			</div>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						{if $article->isMultilingual && $__wcf->user->userID}
							<li class="dropdown">
								<a class="dropdownToggle boxFlag box24 button">
									<span><img src="{$articleContent->getLanguage()->getIconPath()}" alt="" class="iconFlag"></span>
									<span>{$articleContent->getLanguage()->languageName}</span>
								</a>
								<ul class="dropdownMenu">
									{foreach from=$article->getLanguageLinks() item='langArticleContent'}
										{if $langArticleContent->getLanguage()}
											<li class="boxFlag">
												<a class="box24" href="{$langArticleContent->getLink()}">
													<span><img src="{$langArticleContent->getLanguage()->getIconPath()}" alt="" class="iconFlag"></span>
													<span>{$langArticleContent->getLanguage()->languageName}</span>
												</a>
											</li>
										{/if}
									{/foreach}
								</ul>
							</li>
						{/if}
					
						{if $__wcf->getSession()->getPermission('admin.content.article.canManageArticle')}<li><a href="{link controller='ArticleEdit' id=$article->articleID isACP=true}{/link}" class="button"><span class="icon icon16 fa-pencil"></span> <span>{lang}wcf.acp.article.edit{/lang}</span></a></li>{/if}
						{event name='contentHeaderNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</header>
{/capture}

{capture assign='headContent'}
	{if $article->isMultilingual}
		{foreach from=$article->getLanguageLinks() item='langArticleContent'}
			{if $langArticleContent->getLanguage()}
				<link rel="alternate" hreflang="{$langArticleContent->getLanguage()->languageCode}" href="{$langArticleContent->getLink()}">
			{/if}
		{/foreach}
	{/if}
	<link rel="amphtml" href="{link controller='ArticleAmp' object=$articleContent}{/link}">
{/capture}

{include file='header'}

<div class="section">
	{if $articleContent->getImage() && $articleContent->getImage()->hasThumbnail('large')}
		<div class="section" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
			<figure class="articleImage">
				<div class="articleImageWrapper">{@$articleContent->getImage()->getThumbnailTag('large')}</div>
				{if $articleContent->getImage()->caption}
					<figcaption itemprop="description">{$articleContent->getImage()->caption}</figcaption>
				{/if}
			</figure>
			<meta itemprop="url" content="{$articleContent->getImage()->getThumbnailLink('large')}">
			<meta itemprop="width" content="{@$articleContent->getImage()->getThumbnailWidth('large')}">
			<meta itemprop="height" content="{@$articleContent->getImage()->getThumbnailHeight('large')}">
		</div>
	{/if}
	
	{event name='beforeArticleContent'}
	
	<div class="section articleContent"
	         data-object-id="{@$article->articleID}"
	         data-object-type="com.woltlab.wcf.likeableArticle"
	         data-like-liked="{if $articleLikeData[$article->articleID]|isset}{@$articleLikeData[$article->articleID]->liked}{/if}"
	         data-like-likes="{if $articleLikeData[$article->articleID]|isset}{@$articleLikeData[$article->articleID]->likes}{else}0{/if}"
	         data-like-dislikes="{if $articleLikeData[$article->articleID]|isset}{@$articleLikeData[$article->articleID]->dislikes}{else}0{/if}"
	         data-like-users='{ {if $articleLikeData[$article->articleID]|isset}{implode from=$articleLikeData[$article->articleID]->getUsers() item=likeUser}"{@$likeUser->userID}": "{$likeUser->username|encodeJSON}"{/implode}{/if} }'
	         data-user-id="{@$article->userID}"
	>
		<div class="htmlContent">
			{if $articleContent->teaser}
				<p class="articleTeaser">{@$articleContent->getFormattedTeaser()}</p>
			{/if}
		
			{@$articleContent->getFormattedContent()}
			
			{event name='htmlArticleContent'}
		</div>
		
		{if !$tags|empty}
			<ul class="tagList articleTagList">
				{foreach from=$tags item=tag}
					<li><a href="{link controller='Tagged' object=$tag}objectType=com.woltlab.wcf.article{/link}" class="tag">{$tag->name}</a></li>
				{/foreach}
			</ul>
		{/if}
		
		<div class="row articleLikeSection">
			<div class="col-xs-12 col-md-6">
				<div class="articleLikesSummery"></div>
			</div>
			<div class="col-xs-12 col-md-6">
				<ul class="articleLikeButtons buttonGroup"></ul>
			</div>
		</div>
	</div>
	
	{event name='afterArticleContent'}
	
	{if ARTICLE_SHOW_ABOUT_AUTHOR && $article->getUserProfile()->aboutMe}
		<div class="section articleAboutAuthor">
			<h2 class="sectionTitle">{lang}wcf.article.aboutAuthor{/lang}</h2>
			
			<div class="box128">
				<span class="articleAboutAuthorAvatar">{@$article->getUserProfile()->getAvatar()->getImageTag(128)}</span>
				
				<div>
					<div class="articleAboutAuthorText">{@$article->getUserProfile()->getFormattedUserOption('aboutMe')}</div>
					
					<div class="articleAboutAuthorUsername">
						<a href="{link controller='User' object=$article->getUserProfile()->getDecoratedObject()}{/link}" class="username userLink" data-user-id="{@$article->getUserProfile()->userID}">{if MESSAGE_SIDEBAR_ENABLE_USER_ONLINE_MARKING}{@$article->getUserProfile()->getFormattedUsername()}{else}{$article->getUserProfile()->username}{/if}</a>
						
						{if MODULE_USER_RANK}
							{if $article->getUserProfile()->getUserTitle()}
								<span class="badge userTitleBadge{if $article->getUserProfile()->getRank() && $article->getUserProfile()->getRank()->cssClassName} {@$article->getUserProfile()->getRank()->cssClassName}{/if}">{$article->getUserProfile()->getUserTitle()}</span>
							{/if}
							{if $article->getUserProfile()->getRank() && $article->getUserProfile()->getRank()->rankImage}
								<span class="userRank">{@$article->getUserProfile()->getRank()->getImage()}</span>
							{/if}
						{/if}
					</div>
				</div>
			</div>
		</div>
	{/if}
</div>

{if ENABLE_SHARE_BUTTONS}
	{capture assign='footerBoxes'}
		<section class="box boxFullWidth jsOnly">
			<h2 class="boxTitle">{lang}wcf.message.share{/lang}</h2>
			
			<div class="boxContent">
				{include file='shareButtons'}
			</div>
		</section>
	{/capture}
{/if}

<footer class="contentFooter">
	{hascontent}
		<nav class="contentFooterNavigation">
			<ul>
				{content}{event name='contentFooterNavigation'}{/content}
			</ul>
		</nav>
	{/hascontent}
</footer>

{event name='afterFooter'}

{if $previousArticle || $nextArticle}
	<div class="section articleNavigation">
		<nav>
			<ul>
				{if $previousArticle}
					<li class="previousArticleButton">
						<a href="{$previousArticle->getLink()}" rel="prev">
							{if $previousArticle->getTeaserImage()}
								<div class="box96">
									<span class="articleNavigationArticleImage">{@$previousArticle->getTeaserImage()->getElementTag(96)}</span>
									
									<div>
										<span class="articleNavigationEntityName">{lang}wcf.article.previousArticle{/lang}</span>
										<span class="articleNavigationArticleTitle">{$previousArticle->getTitle()}</span>
									</div>
								</div>
							{else}
								<div>
									<span class="articleNavigationEntityName">{lang}wcf.article.previousArticle{/lang}</span>
									<span class="articleNavigationArticleTitle">{$previousArticle->getTitle()}</span>
								</div>
							{/if}
						</a>
					</li>
				{/if}
				
				{if $nextArticle}
					<li class="nextArticleButton">
						<a href="{$nextArticle->getLink()}" rel="next">
							{if $nextArticle->getTeaserImage()}
								<div class="box96">
									<span class="articleNavigationArticleImage">{@$nextArticle->getTeaserImage()->getElementTag(96)}</span>
									
									<div>
										<span class="articleNavigationEntityName">{lang}wcf.article.nextArticle{/lang}</span>
										<span class="articleNavigationArticleTitle">{$nextArticle->getTitle()}</span>
									</div>
								</div>
							{else}
								<div>
									<span class="articleNavigationEntityName">{lang}wcf.article.nextArticle{/lang}</span>
									<span class="articleNavigationArticleTitle">{$nextArticle->getTitle()}</span>
								</div>
							{/if}
						</a>
					</li>
				{/if}
			</ul>
		</nav>
	</div>
{/if}

{if $relatedArticles !== null && $relatedArticles|count}
	<section class="section relatedArticles">
		<h2 class="sectionTitle">{lang}wcf.article.relatedArticles{/lang}</h2>
		
		<ul class="articleList">
			{foreach from=$relatedArticles item='relatedArticle'}
				<li>
					<a href="{$relatedArticle->getLink()}">
						{if $relatedArticle->getTeaserImage() && $relatedArticle->getTeaserImage()->hasThumbnail('tiny')}
							<div class="box128">
								<div class="articleListImage">{@$relatedArticle->getTeaserImage()->getThumbnailTag('tiny')}</div>
						{/if}
						
						<div>
							<div class="containerHeadline">
								<h3 class="articleListTitle">{$relatedArticle->getTitle()}</h3>
								<ul class="inlineList articleListMetaData">
									<li>
										<span class="icon icon16 fa-clock-o"></span>
										{@$relatedArticle->time|time}
									</li>
									
									{if $relatedArticle->enableComments}
										<li>
											<span class="icon icon16 fa-comments"></span>
											{lang article=$relatedArticle}wcf.article.articleComments{/lang}
										</li>
									{/if}
									
									{if MODULE_LIKE && $__wcf->getSession()->getPermission('user.like.canViewLike')}
										<li class="wcfLikeCounter{if $relatedArticle->cumulativeLikes > 0} likeCounterLiked{elseif $relatedArticle->cumulativeLikes < 0}likeCounterDisliked{/if}">
											{if $relatedArticle->likes || $relatedArticle->dislikes}
												<span class="icon icon16 fa-thumbs-o-{if $relatedArticle->cumulativeLikes < 0}down{else}up{/if} jsTooltip" title="{lang likes=$relatedArticle->likes dislikes=$relatedArticle->dislikes}wcf.like.tooltip{/lang}"></span>{if $relatedArticle->cumulativeLikes > 0}+{elseif $relatedArticle->cumulativeLikes == 0}&plusmn;{/if}{#$relatedArticle->cumulativeLikes}
											{/if}
										</li>
									{/if}
								</ul>
							</div>
							
							<div class="containerContent articleListTeaser">
								{@$relatedArticle->getFormattedTeaser()}
							</div>
						</div>
								
						{if $relatedArticle->getTeaserImage() && $relatedArticle->getTeaserImage()->hasThumbnail('tiny')}
							</div>
						{/if}
					</a>
				</li>
			{/foreach}
		</ul>
	</section>
{/if}

{event name='beforeComments'}

{if $article->enableComments}
	{if $commentList|count || $commentCanAdd}
		<section id="comments" class="section sectionContainerList">
			<h2 class="sectionTitle">{lang}wcf.global.comments{/lang}{if $article->comments} <span class="badge">{#$article->comments}</span>{/if}</h2>
			
			{include file='__commentJavaScript' commentContainerID='articleCommentList'}
			
			<ul id="articleCommentList" class="commentList containerList" data-can-add="{if $commentCanAdd}true{else}false{/if}" data-object-id="{@$articleContentID}" data-object-type-id="{@$commentObjectTypeID}" data-comments="{@$commentList->countObjects()}" data-last-comment-time="{@$lastCommentTime}">
				{if $commentCanAdd}{include file='commentListAddComment' wysiwygSelector='articleCommentListAddComment'}{/if}
				{include file='commentList'}
			</ul>
		</section>
	{/if}
{/if}

{if MODULE_LIKE && ARTICLE_ENABLE_LIKE}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Like/Handler'], function(UiLikeHandler) {
			new UiLikeHandler('com.woltlab.wcf.likeableArticle', {
				// settings
				isSingleItem: true,
				
				// permissions
				canDislike: {if LIKE_ENABLE_DISLIKE}true{else}false{/if},
				canLike: {if $__wcf->getUser()->userID}true{else}false{/if},
				canLikeOwnContent: {if LIKE_ALLOW_FOR_OWN_CONTENT}true{else}false{/if},
				canViewSummary: {if LIKE_SHOW_SUMMARY}true{else}false{/if},
				
				// selectors
				badgeContainerSelector: '.articleLikesBadge',
				buttonAppendToSelector: '.articleLikeButtons',
				containerSelector: '.articleContent',
				summarySelector: '.articleLikesSummery'
			});
		});
	</script>
{/if}

{include file='footer'}
