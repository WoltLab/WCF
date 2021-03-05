{if $object->status == "SUCCESSFUL"}
	<div class="
		unfurlUrlCard
		{if $object->hasCoverImage()}unfurlUrlCardCoverImage{/if}
		{if $object->hasSquaredImage()}unfurlUrlCardSquaredImage{/if}
	">
		
		<div{if !$object->getImageUrl()|empty} style="background-image: url('{$object->getImageUrl()}')"{/if}></div>
		<div class="unfurlUrlInformation">
			<div class="unfurlUrlTitle">{$object->title}</div>
			<div class="unfurlUrlDescription">{$object->description}</div>
			<div class="unfurlUrlHost">{$object->getHost()}</div>
		</div>
		<a class="unfurlUrlLink" {anchorAttributes url=$object->url appendClassname=false isUgc=true}></a>
	</div>
{else}
	<a {anchorAttributes url=$object->url isUgc=true}>{$object->url}</a>
{/if}