{if $object->hasFetchedContent()}
	{if $object->isPlainUrl()}
		<a {anchorAttributes url=$object->url isUgc=$enableUgc}>{$object->title}</a>
	{else}
		<div class="unfurlUrlCardContainer">
			<div class="unfurlUrlCard{*
				*}{if $object->hasCoverImage()} unfurlUrlCardCoverImage{/if}{*
				*}{if $object->hasSquaredImage()} unfurlUrlCardSquaredImage{/if}{*
			*}">
				{if !$object->getImageUrl()|empty}
					<img src="{$object->getImageUrl()}" height="{$object->height}" width="{$object->width}" class="unfurlUrlImage" alt="" loading="lazy">
				{/if}
				<div class="unfurlUrlInformation">
					<a class="unfurlUrlTitle" {anchorAttributes url=$object->url appendClassname=false isUgc=$enableUgc}>{$object->title}</a>
					<div class="unfurlUrlDescription">{$object->description}</div>
					<div class="unfurlUrlHost">{$object->getHost()}</div>
				</div>
			</div>
		</div>
	{/if}
{else}
	<a {anchorAttributes url=$object->url isUgc=$enableUgc}>{$object->url}</a>
{/if}