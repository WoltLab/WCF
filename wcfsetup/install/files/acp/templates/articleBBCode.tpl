<article class="articleEmbeddedEntry embeddedContent" aria-labelledby="{$titleHash}_entryTitle{$article->articleID}">
	<div class="embeddedContentLink">
		<img
			class="embeddedContentImageElement"
			src="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailLink('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoURL()}{/if}"
			height="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailHeight('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoHeight()}{/if}"
			width="{if $article->getTeaserImage()}{$article->getTeaserImage()->getThumbnailWidth('medium')}{else}{$__wcf->getStyleHandler()->getStyle()->getCoverPhotoWidth()}{/if}"
			loading="lazy"
			alt="">

		<div class="embeddedContentCategory">{lang}wcf.article.bbcode.type{/lang}</div>
		
		<h3 class="embeddedContentTitle" id="{$titleHash}_articleTitle{$article->articleID}">
			<a href="{$article->getLink()}" class="embeddedContentTitleLink">{$article->getTitle()}</a>
		</h3>
		
		<div class="embeddedContentDescription">
			{@$article->getFormattedTeaser()}
		</div>
	</div>
	
	<div class="embeddedContentMeta">
		<div class="embeddedContentMetaImage">
			{@$article->getUserProfile()->getAvatar()->getImageTag(32)}
		</div>
		
		<div class="embeddedContentMetaContent">
			<div class="embeddedContentMetaAuthor">
				{@$article->getUserProfile()->getFormattedUsername()}
			</div>
			
			<div class="embeddedContentMetaTime">
				{time time=$article->time}
			</div>
		</div>
	</div>
</article>
