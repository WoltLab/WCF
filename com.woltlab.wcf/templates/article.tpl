{capture assign='pageTitle'}{if $articleContent->metaTitle}{$articleContent->metaTitle}{else}{$articleContent->title}{/if}{/capture}

{assign var='__mainItemScope' value='itemprop="mainEntity" itemscope itemtype="http://schema.org/Article"'}

{capture assign='contentHeader'}
	<header class="contentHeader articleContentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle" itemprop="name headline">{$articleContent->title}</h1>
			<ul class="inlineList contentHeaderMetaData articleMetaData">
				{if $article->hasLabels()}
					<li>
						{icon name='tags'}
						<ul class="labelList">
							{foreach from=$article->getLabels() item=label}
								<li>{@$label->render()}</li>
							{/foreach}
						</ul>
					</li>
				{/if}
				
				<li itemprop="author" itemscope itemtype="http://schema.org/Person">
					{icon name='user'}
					{if $article->userID}
						<a href="{$article->getUserProfile()->getLink()}" class="userLink" data-object-id="{@$article->userID}" itemprop="url">
							<span itemprop="name">{@$article->getUserProfile()->getFormattedUsername()}</span>
						</a>
					{else}
						<span itemprop="name">{$article->username}</span>
					{/if}
				</li>
				
				<li>
					{icon name='clock'}
					<a href="{$article->getLink()}">{@$article->time|time}</a>
					<meta itemprop="datePublished" content="{@$article->time|date:'c'}">
				</li>

				<li>
					{icon name='eye'}
					{lang}wcf.article.articleViews{/lang}
				</li>
				
				{if $article->getDiscussionProvider()->getDiscussionCountPhrase()}
					<li itemprop="interactionStatistic" itemscope itemtype="http://schema.org/InteractionCounter">
						{icon name='comments'}
						{if $article->getDiscussionProvider()->getDiscussionLink()}<a href="{$article->getDiscussionProvider()->getDiscussionLink()}">{else}<span>{/if}
						{$article->getDiscussionProvider()->getDiscussionCountPhrase()}
						{if $article->getDiscussionProvider()->getDiscussionLink()}</a>{else}</span>{/if}
						<meta itemprop="interactionType" content="http://schema.org/CommentAction">
						<meta itemprop="userInteractionCount" content="{@$article->getDiscussionProvider()->getDiscussionCount()}">
					</li>
				{/if}
				
				{if $article->isNew()}<li><span class="badge label newMessageBadge">{lang}wcf.message.new{/lang}</span></li>{/if}
				
				{if $article->isDeleted}<li><span class="badge label red">{lang}wcf.message.status.deleted{/lang}</span></li>{/if}
				
				{event name='contentHeaderMetaData'}
			</ul>
			
			<div itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
				<meta itemprop="name" content="{PAGE_TITLE|phrase}">
				<div itemprop="logo" itemscope itemtype="http://schema.org/ImageObject">
					<meta itemprop="url" content="{$__wcf->getStyleHandler()->getStyle()->getPageLogo()}">
				</div>
			</div>
		</div>
		
		{hascontent}
			<nav class="contentHeaderNavigation">
				<ul>
					{content}
						{if $article->canEdit()}<li><a href="{link controller='ArticleEdit' id=$article->articleID}{/link}" class="button buttonPrimary">{icon name='pencil'} <span>{lang}wcf.acp.article.edit{/lang}</span></a></li>{/if}
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
	<woltlab-core-notice type="info">{lang publicationDate=$article->publicationDate}wcf.article.publicationStatus.{@$article->publicationStatus}{/lang}</woltlab-core-notice>
{/if}

<div class="section articleContainer">
	{if $articleContent->teaser}
		<div class="section articleTeaserContainer">
			<div class="htmlContent">
				<p class="articleTeaser">{@$articleContent->getFormattedTeaser()}</p>
			</div>
		</div>
	{/if}
	
	{if $articleContent->getImage() && $articleContent->getImage()->hasThumbnail('large')}
		<div class="section articleImageContainer" itemprop="image" itemscope itemtype="http://schema.org/ImageObject">
			<figure class="articleImage">
				<div class="articleImageWrapper">{@$articleContent->getImage()->getThumbnailTag('large')}</div>
				{if $articleContent->getImage()->caption}
					<figcaption itemprop="description">
						{if $articleContent->getImage()->captionEnableHtml}
							{@$articleContent->getImage()->caption}
						{else}
							{$articleContent->getImage()->caption}
						{/if}
					</figcaption>
				{/if}
			</figure>
			<meta itemprop="url" content="{$articleContent->getImage()->getThumbnailLink('large')}">
			<meta itemprop="width" content="{@$articleContent->getImage()->getThumbnailWidth('large')}">
			<meta itemprop="height" content="{@$articleContent->getImage()->getThumbnailHeight('large')}">
		</div>
	{/if}
	
	{event name='beforeArticleContent'}
	
	<div class="section articleContent" {@$__wcf->getReactionHandler()->getDataAttributes('com.woltlab.wcf.likeableArticle', $article->articleID)}>
		<div class="section htmlContent" itemprop="description articleBody">
			{if MODULE_WCF_AD}
				{@$__wcf->getAdHandler()->getAds('com.woltlab.wcf.article.inArticle')}
			{/if}
			
			{@$articleContent->getFormattedContent()}
			
			{event name='htmlArticleContent'}
		</div>

		{include file='attachments' objectID=$article->articleID}
		
		{if !$tags|empty}
			<ul class="tagList articleTagList">
				{foreach from=$tags item=tag}
					<li><a href="{link controller='Tagged' object=$tag}objectType=com.woltlab.wcf.article{/link}" class="tag">{$tag->name}</a></li>
				{/foreach}
			</ul>
		{/if}
		
		<div class="row articleLikeSection">
			{if MODULE_LIKE && ARTICLE_ENABLE_LIKE && $__wcf->session->getPermission('user.like.canViewLike')}
				<div class="col-xs-12 col-md-6">
					<div class="articleLikesSummery">
						{include file="reactionSummaryList" reactionData=$articleLikeData objectType="com.woltlab.wcf.likeableArticle" objectID=$article->articleID}
					</div>
				</div>
			{/if}
			
			<div class="col-xs-12 col-md-6 col-md{if !(MODULE_LIKE && ARTICLE_ENABLE_LIKE && $__wcf->session->getPermission('user.like.canViewLike'))} col-md-offset-6{/if}">
				<ul class="articleButtons buttonGroup buttonList smallButtons">
					{if $__wcf->session->getPermission('user.profile.canReportContent')}
						<li>
							<button
								type="button"
								title="{lang}wcf.moderation.report.reportContent{/lang}"
								class="button jsTooltip"
								data-report-content="com.woltlab.wcf.article"
								data-object-id="{$articleContent->articleID}"
							>
								{icon name='triangle-exclamation'}
								<span class="invisible">{lang}wcf.moderation.report.reportContent{/lang}</span>
							</button>
						</li>
					{/if}
					{if MODULE_LIKE && ARTICLE_ENABLE_LIKE && $__wcf->session->getPermission('user.like.canLike') && $article->userID != $__wcf->user->userID}
						<li class="jsOnly"><span class="button jsTooltip reactButton{if $articleLikeData[$article->articleID]|isset && $articleLikeData[$article->articleID]->reactionTypeID} active{/if}" title="{lang}wcf.reactions.react{/lang}" data-reaction-type-id="{if $articleLikeData[$article->articleID]|isset && $articleLikeData[$article->articleID]->reactionTypeID}{$articleLikeData[$article->articleID]->reactionTypeID}{else}0{/if}">{icon name='face-smile'} <span class="invisible">{lang}wcf.reactions.react{/lang}</span></span></li>
					{/if}
					{event name='articleLikeButtons'}{* deprecated: use articleButtons instead *}
					{event name='articleButtons'}
				</ul>
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
					{event name='beforeAboutAuthorText'}
					
					<div class="articleAboutAuthorText">{@$article->getUserProfile()->getFormattedUserOption('aboutMe')}</div>
					
					{event name='afterAboutAuthorText'}
					
					<div class="articleAboutAuthorUsername">
						{user object=$article->getUserProfile() class='username'}
						
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
	<div class="section">
		<nav>
			<ul class="articleNavigation">
				{if $previousArticle}
					<li class="previousArticleButton articleNavigationArticle{if $previousArticle->getTeaserImage()} articleNavigationArticleWithImage{/if}">
						<span class="articleNavigationArticleIcon">
							{icon size=48 name='chevron-left'}
						</span>
						{if $previousArticle->getTeaserImage()}
							<span class="articleNavigationArticleImage">{@$previousArticle->getTeaserImage()->getElementTag(96)}</span>
						{/if}
						<span class="articleNavigationArticleContent">
							<span class="articleNavigationEntityName">{lang}wcf.article.previousArticle{/lang}</span>
							<span class="articleNavigationArticleTitle">
								<a href="{$previousArticle->getLink()}" rel="prev" class="articleNavigationArticleLink">
									{$previousArticle->getTitle()}
								</a>
							</span>
						</span>
					</li>
				{/if}
				
				{if $nextArticle}
					<li class="nextArticleButton articleNavigationArticle{if $nextArticle->getTeaserImage()} articleNavigationArticleWithImage{/if}">
						<span class="articleNavigationArticleIcon">
							{icon size=48 name='chevron-right'}
						</span>
						{if $nextArticle->getTeaserImage()}
							<span class="articleNavigationArticleImage">{@$nextArticle->getTeaserImage()->getElementTag(96)}</span>
						{/if}
						<span class="articleNavigationArticleContent">
							<span class="articleNavigationEntityName">{lang}wcf.article.nextArticle{/lang}</span>
							<span class="articleNavigationArticleTitle">
								<a href="{$nextArticle->getLink()}" rel="next" class="articleNavigationArticleLink">
									{$nextArticle->getTitle()}
								</a>
							</span>
						</span>
					</li>
				{/if}
			</ul>
		</nav>
	</div>
{/if}

{if $relatedArticles !== null && $relatedArticles|count}
	<section class="section relatedArticles">
		<h2 class="sectionTitle">{lang}wcf.article.relatedArticles{/lang}</h2>
		
		{include file='articleListItems' objects=$relatedArticles}
	</section>
{/if}

{event name='beforeComments'}

{@$article->getDiscussionProvider()->renderDiscussions()}

{if MODULE_LIKE && ARTICLE_ENABLE_LIKE}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/Reaction/Handler'], function(UiReactionHandler) {
			new UiReactionHandler('com.woltlab.wcf.likeableArticle', {
				// permissions
				canReact: {if $__wcf->getUser()->userID}true{else}false{/if},
				canReactToOwnContent: false,
				canViewReactions: {if LIKE_SHOW_SUMMARY}true{else}false{/if},
				
				// selectors
				containerSelector: '.articleContent',
				summarySelector: '.articleLikesSummery'
			});
		});
	</script>
{/if}

{include file='footer'}
