<article class="articleEmbeddedEntry embeddedContent" aria-labelledby="{$titleHash}_entryTitle{@$article->articleID}">
	<div class="embeddedContentLink">
		<img
			class="embeddedContentImageElement"
			src="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailLink('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoURL()}{/if}"
			height="{if $article->getTeaserImage()}{@$article->getTeaserImage()->getThumbnailHeight('medium')}{else}{@$__wcf->getStyleHandler()->getStyle()->getCoverPhotoHeight()}{/if}"
			width="{if $article->getTeaserImage()}{@$article->getTeaserImage()->getThumbnailWidth('medium')}{else}{@$__wcf->getStyleHandler()->getStyle()->getCoverPhotoWidth()}{/if}"
			loading="lazy"
			alt="">

        <div class="embeddedContentCategory">{lang}wcf.article.bbcode.type{/lang}</div>
		
		<div class="embeddedContentTitle" id="{$titleHash}_articleTitle{@$article->articleID}">{$article->getTitle()}</div>
		
		<div class="embeddedContentDescription">
			{@$article->getFormattedTeaser()}
		</div>

		<a href="{@$article->getLink()}" class="embeddedContentLinkShadow"></a>
	</div>
	
	<div class="embeddedContentMeta">
		{user object=$article->getUserProfile() type='avatar32' class='embeddedContentMetaImage' ariaHidden='true' tabindex='-1'}
		
		<div class="embeddedContentMetaContent">
			<div class="embeddedContentMetaAuthor">
				{user object=$article->getUserProfile() class='username'}
			</div>
			
			<div class="embeddedContentMetaTime">
				{@$article->time|time}
			</div>
		</div>
	</div>
</article>
