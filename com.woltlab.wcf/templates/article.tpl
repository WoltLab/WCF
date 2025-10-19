{capture assign='pageTitle'}{if $articleContent->metaTitle}{$articleContent->metaTitle}{else}{$articleContent->title}{/if}{/capture}

{assign var='__mainItemScope' value='itemprop="mainEntity" itemscope itemtype="http://schema.org/Article"'}

{capture assign='contentHeader'}
	<header class="contentHeader articleContentHeader">
		{include file='articleContentHeaderTitle'}
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
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
{/capture}

{capture assign='contentInteractionButtons'}
	{unsafe:$interactionContextMenu->render()}
	
	{if $article->isMultilingual && $__wcf->user->userID}
		<div class="contentInteractionButton dropdown jsOnly">
			<button type="button" class="dropdownToggle boxFlag box24 button small">
				<span><img src="{$articleContent->getLanguage()->getIconPath()}" alt="" class="iconFlag"></span>
				<span>{$articleContent->getLanguage()->languageName}</span>
			</button>
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
		</div>
	{/if}
{/capture}

{capture assign='contentInteractionShareButton'}
	<button type="button" class="button small wsShareButton jsTooltip" title="{lang}wcf.message.share{/lang}" data-link="{$articleContent->getLink()}" data-link-title="{$articleContent->getTitle()}" data-bbcode="[wsa]{$article->getObjectID()}[/wsa]">
		{icon name='share-nodes'}
	</button>
{/capture}

{include file='header'}

{if !$article->isPublished()}
	<woltlab-core-notice type="info">{lang publicationDate=$article->publicationDate}wcf.article.publicationStatus.{$article->publicationStatus}{/lang}</woltlab-core-notice>
{/if}

<div
	class="section entry article"
	{unsafe:$__wcf->getReactionHandler()->getDataAttributes('com.woltlab.wcf.likeableArticle', $article->articleID)}
>
	{if $articleContent->teaser}
		<div class="entry__teaser htmlContent">
			{unsafe:$articleContent->getFormattedTeaser()}
		</div>
	{/if}
	
	{if $articleContent->getImage() && $articleContent->getImage()->hasThumbnail('large')}
		<div class="entry__coverPhoto__wrapper" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
			<figure class="entry__coverPhoto">
				{unsafe:$articleContent->getImage()->getThumbnailTag('large')}
				{if $articleContent->getImage()->caption}
					<figcaption itemprop="description">
						{if $articleContent->getImage()->captionEnableHtml}
							{unsafe:$articleContent->getImage()->caption}
						{else}
							{$articleContent->getImage()->caption}
						{/if}
					</figcaption>
				{/if}
			</figure>
			<meta itemprop="url" content="{$articleContent->getImage()->getThumbnailLink('large')}">
			<meta itemprop="width" content="{$articleContent->getImage()->getThumbnailWidth('large')}">
			<meta itemprop="height" content="{$articleContent->getImage()->getThumbnailHeight('large')}">
		</div>
	{/if}
	
	{event name='beforeArticleContent'}

	<div class="entry__content htmlContent" itemprop="description articleBody">
		{if MODULE_WCF_AD}
			{unsafe:$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.inArticle')}
		{/if}
		
		{unsafe:$articleContent->getFormattedContent()}
		
		{event name='htmlArticleContent'}
	</div>

	{event name='afterArticleContent'}

	{include file='entryTags' objectType='com.woltlab.wcf.article'}

	{include file='entryAttachments' objectID=$article->articleID}

	<footer class="entry__footer">
		{if MODULE_LIKE && ARTICLE_ENABLE_LIKE && $__wcf->session->getPermission('user.like.canViewLike')}
			<div class="articleLikesSummery">
				{include file="reactionSummaryList" reactionData=$articleLikeData objectType="com.woltlab.wcf.likeableArticle" objectID=$article->articleID}
			</div>
		{/if}
		
		{hascontent}
			<div class="entry__footerButtons">
				{content}
					{if $__wcf->session->getPermission('user.profile.canReportContent')}
						<button
							type="button"
							class="button jsTooltip"
							title="{lang}wcf.moderation.report.reportContent{/lang}"
							data-report-content="com.woltlab.wcf.article"
							data-object-id="{$articleContent->articleID}"
						>
							{icon name='triangle-exclamation'}
						</button>
					{/if}
					{if MODULE_LIKE && ARTICLE_ENABLE_LIKE && $__wcf->session->getPermission('user.like.canLike') && $article->userID != $__wcf->user->userID}
						<button
							type="button"
							class="button jsTooltip reactButton{if $articleLikeData[$article->articleID]|isset && $articleLikeData[$article->articleID]->reactionTypeID} active{/if}"
							title="{lang}wcf.reactions.react{/lang}"
							data-reaction-type-id="{if $articleLikeData[$article->articleID]|isset && $articleLikeData[$article->articleID]->reactionTypeID}{$articleLikeData[$article->articleID]->reactionTypeID}{else}0{/if}"
						>
							{icon name='face-smile'}
						</button>
					{/if}
					
					{event name='articleLikeButtons'}{* deprecated: use footerButtons instead *}
					{event name='articleButtons'}{* deprecated: use footerButtons instead *}
					{event name='footerButtons'}
				{/content}
			</div>
		{/hascontent}
	</footer>
</div>

{if ARTICLE_SHOW_ABOUT_AUTHOR && $article->getUserProfile()->aboutMe}
	<section class="section entry__aboutAuthor__container">
		<h2 class="sectionTitle">{lang}wcf.article.aboutAuthor{/lang}</h2>
		
		<div class="entry__aboutAuthor">
			<div class="entry__aboutAuthor__avatar">
				{unsafe:$article->getUserProfile()->getAvatar()->getImageTag(128)}
			</div>
			
			<div class="entry__aboutAuthor__content">
				{event name='beforeAboutAuthorText'}
				
				<div class="entry__aboutAuthor__text">
					{unsafe:$article->getUserProfile()->getFormattedUserOption('aboutMe')}
				</div>
				
				{event name='afterAboutAuthorText'}
				
				<div class="entry__aboutAuthor__username">
					{user object=$article->getUserProfile() class='username'}
					
					{if MODULE_USER_RANK}
						{if $article->getUserProfile()->getUserTitle()}
							<span class="badge userTitleBadge{if $article->getUserProfile()->getRank() && $article->getUserProfile()->getRank()->cssClassName} {$article->getUserProfile()->getRank()->cssClassName}{/if}">{$article->getUserProfile()->getUserTitle()}</span>
						{/if}
						{if $article->getUserProfile()->getRank() && $article->getUserProfile()->getRank()->rankImage}
							<span class="userRank">{unsafe:$article->getUserProfile()->getRank()->getImage()}</span>
						{/if}
					{/if}
				</div>
			</div>
		</div>
	</section>
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
	<div class="section entry__navigation">
		{if $previousArticle}
			<div class="entry__navigation__item entry__navigation__item--previous{if $previousArticle->getTeaserImage()} entry__navigation__item--withImage{/if}">
				<div class="entry__navigation__item__icon">
					{icon size=48 name='chevron-left'}
				</div>
				{if $previousArticle->getTeaserImage()}
					<div class="entry__navigation__item__image">{unsafe:$previousArticle->getTeaserImage()->getElementTag(96)}</div>
				{/if}
				<div class="entry__navigation__item__content">
					<div class="entry__navigation__item__entityName">{lang}wcf.article.previousArticle{/lang}</div>
					<div class="entry__navigation__item__title">
						<a href="{$previousArticle->getLink()}" rel="prev" class="entry__navigation__item__link articleLink" data-object-id="{$previousArticle->getObjectID()}">
							{$previousArticle->getTitle()}
						</a>
					</div>
				</div>
			</div>
		{/if}
		{if $nextArticle}
			<div class="entry__navigation__item entry__navigation__item--next{if $nextArticle->getTeaserImage()} entry__navigation__item--withImage{/if}">
				<div class="entry__navigation__item__icon">
					{icon size=48 name='chevron-right'}
				</div>
				{if $nextArticle->getTeaserImage()}
					<div class="entry__navigation__item__image">{unsafe:$nextArticle->getTeaserImage()->getElementTag(96)}</div>
				{/if}
				<div class="entry__navigation__item__content">
					<div class="entry__navigation__item__entityName">{lang}wcf.article.nextArticle{/lang}</div>
					<div class="entry__navigation__item__title">
						<a href="{$nextArticle->getLink()}" rel="prev" class="entry__navigation__item__link articleLink" data-object-id="{$nextArticle->getObjectID()}">
							{$nextArticle->getTitle()}
						</a>
					</div>
				</div>
			</div>
		{/if}
	</div>
{/if}

{if $relatedArticleListView && $relatedArticleListView->countItems()}
	<section class="section relatedArticles entryCardList__container">
		<h2 class="sectionTitle">{lang}wcf.article.relatedArticles{/lang}</h2>
		
		{unsafe:$relatedArticleListView->render()}
	</section>
{/if}

{event name='beforeComments'}

{unsafe:$article->getDiscussionProvider()->renderDiscussions()}

{if MODULE_LIKE && ARTICLE_ENABLE_LIKE}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
			new UiReactionHandler('com.woltlab.wcf.likeableArticle', {
				// permissions
				canReact: {if $__wcf->getUser()->userID}true{else}false{/if},
				canReactToOwnContent: false,
				canViewReactions: {if LIKE_SHOW_SUMMARY}true{else}false{/if},
				
				// selectors
				containerSelector: '.article',
				summarySelector: '.articleLikesSummery'
			});
		});
	</script>
{/if}

{include file='footer'}
