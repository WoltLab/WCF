{if $__wcf->session->getPermission('admin.general.canUseAcp')}{include file='header' templateName='permissionDenied'}

<p class="error">{lang}wcf.global.error.permissionDenied{/lang}</p>

{include file='footer'}
{else}{capture assign='pageTitle'}{lang}wcf.global.error.permissionDenied.title{/lang}{/capture}
{include file='setupHeader'}

<img class="icon" src="{@RELATIVE_WCF_DIR}icon/loginXL.png" alt="" />

<h1><b>{@$pageTitle}</b></h1>

<hr />

<p class="error">{lang}wcf.global.error.permissionDenied{/lang}</p>

{include file='setupFooter'}
{/if}
