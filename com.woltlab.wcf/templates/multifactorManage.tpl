{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.{$method->objectType}.manage{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.user.security.multifactor.{$method->objectType}.manage{/lang}{/capture}

{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{if $backupForm}
	{if $form->showsSuccessMessage()}
		<p class="success">
			<span>{@$form->getSuccessMessage()}</span>
		</p>
	{/if}
	
	{@$backupForm->getNodeById('existingCodesContainer')->getHtml()}
{else}
	{@$form->getHtml()}
{/if}

{include file='footer' __disableAds=true}
