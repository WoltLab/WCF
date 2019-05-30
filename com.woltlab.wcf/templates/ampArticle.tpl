{capture assign='pageTitle'}{$articleContent->title}{/capture}

{capture assign='headContent'}
	<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "NewsArticle",
			"mainEntityOfPage": "{@$regularCanonicalURL|encodeJSON}",
			"headline": "{@$articleContent->title|encodeJSON}",
			"datePublished": "{@$article->time|date:'c'}",
			"dateModified": "{@$article->time|date:'c'}",
			"description": "{@$articleContent->getFormattedTeaser()|encodeJSON}",
			"author": {
				"@type": "Person",
				"name": "{@$article->username|encodeJSON}"
			},
			"publisher": {
				"@type": "Organization",
				"name": "{PAGE_TITLE|language}",
				"logo": {
					"@type": "ImageObject",
					"url": "{@$__wcf->getStyleHandler()->getStyle()->getPageLogo()|encodeJSON}",
					"width": {@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoWidth')},
					"height": {@$__wcf->getStyleHandler()->getStyle()->getVariable('pageLogoHeight')}
				}
			}
			{if $articleContent->getImage()}
			,"image": {
				"@type": "ImageObject",
				"url": "{@$articleContent->getImage()->getThumbnailLink('large')|encodeJSON}",
				"width": {@$articleContent->getImage()->getThumbnailWidth('large')},
				"height": {@$articleContent->getImage()->getThumbnailHeight('large')}
			}
			{/if}
		}
	</script>
{/capture}

{include file='ampHeader'}

<article class="article">
	<header class="articleHeader">
		<h1 class="articleTitle">{$articleContent->title}</h1>
		<h2 class="articleAuthor">{$article->username}</h2>
		<time class="articleDate" datetime="{@$article->time|date:'c'}">{@$article->time|plainTime}</time>
	</header>
	
	{if $articleContent->getImage()}
		<figure class="articleImage">
			<amp-img src="{$articleContent->getImage()->getThumbnailLink('large')}" alt="{$articleContent->getImage()->altText}" height="{@$articleContent->getImage()->getThumbnailHeight('large')}" width="{@$articleContent->getImage()->getThumbnailWidth('large')}" layout="responsive"></amp-img>
			{if $articleContent->getImage()->caption}
				<figcaption>{$articleContent->getImage()->caption}</figcaption>
			{/if}
		</figure>
	{/if}
	
	{if $articleContent->teaser}
		<div class="articleTeaser">
			<p>{@$articleContent->getFormattedTeaser()}</p>
		</div>
	{/if}
	
	<div class="articleContent">
		{@$articleContent->getAmpFormattedContent()}
	</div>
</article>

{hascontent}
	<section class="section">
		<h2 class="sectionTitle">{lang}wcf.article.moreArticles{/lang}</h2>
		
		<amp-carousel width="400" height="300" layout="responsive" type="slides" autoplay delay="5000">
			{content}
				{foreach from=$additionalArticles item='additionalArticle'}
					{if $additionalArticle->getImage()}
						<a href="{link controller='ArticleAmp' object=$additionalArticle->getArticleContent()}{/link}">
							<figure>
								<amp-img src="{$additionalArticle->getImage()->getThumbnailLink('large')}" layout="fill"></amp-img>
								<figcaption>{$additionalArticle->getTitle()}</figcaption>
							</figure>
						</a>
					{/if}
				{/foreach}
			{/content}
		</amp-carousel>
	</section>
{/hascontent}

{if $relatedArticles !== null && $relatedArticles|count}
	{hascontent}
		<section class="section">
			<h2 class="sectionTitle">{lang}wcf.article.relatedArticles{/lang}</h2>
			
			<amp-carousel width="400" height="300" layout="responsive" type="slides" autoplay delay="5000">
				{content}
					{foreach from=$relatedArticles item='relatedArticle'}
						{if $relatedArticle->getImage()}
							<a href="{link controller='ArticleAmp' object=$relatedArticle->getArticleContent()}{/link}">
								<figure>
									<amp-img src="{$relatedArticle->getImage()->getThumbnailLink('large')}" layout="fill"></amp-img>
									<figcaption>{$relatedArticle->getTitle()}</figcaption>
								</figure>
							</a>
						{/if}
					{/foreach}
				{/content}
			</amp-carousel>
		</section>
	{/hascontent}
{/if}

{include file='ampFooter'}
