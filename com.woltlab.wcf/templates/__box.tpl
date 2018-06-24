<{if $box->showHeader}section{else}div{/if} class="box{if $box->getImage()} boxWithImage{/if}{if $box->showEditButton()} boxWithEditButton{/if}{if $box->cssClassName} {$box->cssClassName}{/if}" data-box-identifier="{@$box->identifier}">
	{if $box->showEditButton()}
		<a href="{link controller='BoxEdit' id=$box->boxID isACP=true}{/link}" class="boxEditButton"><span class="icon icon16 fa-pencil-square-o"></span></a>
	{/if}
	{if $box->getImage()}
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
	
	<div class="boxContent{if $box->boxType == 'text'} htmlContent{/if}">
		{@$box->getContent()}
	</div>
{if $box->showHeader}</section>{else}</div>{/if}
