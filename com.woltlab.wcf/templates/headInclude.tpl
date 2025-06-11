<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
{include file='headIncludeRobotsMetaTag'}
{implode from=$__wcf->getMetaTagHandler() item=__metaTag glue="\n"}{unsafe:$__metaTag}{/implode}
{event name='metaTags'}

<!-- Stylesheets -->
{unsafe:$__wcf->getStyleHandler()->getStylesheet()}
{event name='stylesheets'}

<meta name="timezone" content="{$__wcf->user->getTimeZone()->getName()}">

{include file='headIncludeJavaScript'}
{include file='headIncludeIcons'}

{unsafe:HEAD_CODE}
