{capture assign='pageTitle'}{lang}wcf.user.security.multifactor.{$method->objectType}.manage{/lang}{/capture}
{capture assign='contentTitle'}{lang}wcf.user.security.multifactor.{$method->objectType}.manage{/lang}{/capture}

{include file='userMenuSidebar'}

{include file='header' __disableAds=true __sidebarLeftHasMenu=true}

{@$form->getHtml()}

{include file='footer' __disableAds=true}
