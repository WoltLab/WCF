{capture assign='sidebarLeft'}
<section class="box">
		<h2 class="boxTitle">{lang}wcf.user.security.multifactor.methods{/lang}</h2>
		
		<div class="boxContent">
			<nav>
				<ol class="boxMenu">
					{foreach from=$methods key='_setupId' item='method'}
						<li{if $setupId == $_setupId} class="active"{/if}>
							<a class="boxMenuLink" href="{link controller='MultifactorAuthentication' id=$_setupId}{/link}"><span class="boxMenuLinkTitle">{lang}wcf.user.security.multifactor.{$method->objectType}{/lang}</span></a>
						</li>
					{/foreach}
				</ol>
			</nav>
		</div>
	</section>
{/capture}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{$user->username}

{@$form->getHtml()}

{include file='footer' __disableAds=true}
