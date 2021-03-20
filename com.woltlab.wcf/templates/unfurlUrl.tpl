{if $object->status == "SUCCESSFUL"}
	{if $object->isPlainUrl()}
		<a {anchorAttributes url=$object->url isUgc=$enableUgc}>{$object->title}</a>
	{else}
		<div class="
			unfurlUrlCard
			{if $object->hasCoverImage()}unfurlUrlCardCoverImage{/if}
			{if $object->hasSquaredImage()}unfurlUrlCardSquaredImage{/if}
		">

			<div class="unfurlUrlImage"{if !$object->getImageUrl()|empty} style="background-image: url('{$object->getImageUrl()}')"{/if}></div>
			<div class="unfurlUrlInformation">
				<div class="unfurlUrlTitle">{$object->title}</div>
				<div class="unfurlUrlDescription">{$object->description}</div>
				<div class="unfurlUrlHost">{$object->getHost()}</div>
			</div>
			<a class="unfurlUrlLinkShadow" {anchorAttributes url=$object->url appendClassname=false isUgc=$enableUgc}></a>
		</div>
	{/if}
{else}
	<a {anchorAttributes url=$object->url isUgc=$enableUgc}>{$object->url}</a>
{/if}