<{if $box->showHeader}section{else}div{/if} class="box{if $box->hasImage()} boxWithImage{/if}{if $box->cssClassName} {$box->cssClassName}{/if}" data-box-identifier="{@$box->identifier}">
	{if $box->hasImage()}
		<div class="boxImage">
			{if $box->hasLink()}
				<a href="{$box->getLink()}">{@$box->getImage()}</a>
			{else}
				{@$box->getImage()}
			{/if}
		</div>
	{/if}
	
	{if $box->showHeader}
		<h2 class="boxTitle">
			{if $box->hasLink()}
				<a href="{$box->getLink()}">{$box->getTitle()}</a>
			{else}
				{$box->getTitle()}
			{/if}
		</h2>
	{/if}
	
	<div class="boxContent">
		{@$box->getContent()}
	</div>
{if $box->showHeader}</section>{else}</div>{/if}
