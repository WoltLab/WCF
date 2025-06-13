{capture assign='pageTitle'}{$contentTitle}{/capture}

{capture assign='contentHeader'}
	<header class="contentHeader">
		<div class="contentHeaderTitle">
			<h1 class="contentTitle">{$contentTitle}</h1>
		</div>
	</header>
{/capture}

{capture assign='sidebarRight'}
	<section class="box" data-static-box-identifier="com.woltlab.wcf.DeletedContentListMenu">
		<h2 class="boxTitle">{lang}wcf.moderation.deletedContent.objectTypes{/lang}</h2>
		
		<div class="boxContent">
			<ul class="boxMenu">
				{foreach from=$providerLinks item=providerLink}
					<li{if $provider === $providerLink[identifier]} class="active"{/if}>
						<a class="boxMenuLink" href="{$providerLink[link]}">
							<span class="boxMenuLinkTitle">{$providerLink[title]}</span>
						</a>
					</li>
				{/foreach}
			</ul>
		</div>
	</section>
{/capture}

{include file='header'}

<div class="section {$containerCssClassName}">
	{unsafe:$listView->render()}
</div>

{include file='footer'}
