{capture assign='pageTitle'}{$articleContent->title}{/capture}

{capture assign='headContent'}
	<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "NewsArticle",
			"mainEntityOfPage": "{$regularCanonicalURL}",
			"headline": "{$articleContent->title}",
			"datePublished": "{@$article->time|plainTime}",
			"dateModified": "{@$article->time|plainTime}",
			"description": "{@$articleContent->getFormattedTeaser()}",
			"author": {
				"@type": "Person",
				"name": "{$article->username}"
			},
			"publisher": {
				"@type": "Organization",
				"name": "{PAGE_TITLE|language}",
				"logo": {
					"@type": "ImageObject",
					"url": "{@$__wcf->getPath()}images/default-logo.png",{* @TODO *}
					"width": 288,
					"height": 40
				}
			}
			{if $articleContent->getImage()}
			,"image": {
				"@type": "ImageObject",
				"url": "{$articleContent->getImage()->getThumbnailLink('large')}",
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
		{@$articleContent->getFormattedContent()}
	</div>
</article>
	
{include file='ampFooter'}
