<meta charset="utf-8" />
<meta name="description" content="{META_DESCRIPTION}" />
<meta name="keywords" content="{META_KEYWORDS}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<script type="text/javascript">
	//<![CDATA[
	var SID_ARG_2ND	= '{@SID_ARG_2ND_NOT_ENCODED}';
	var RELATIVE_WCF_DIR = '{@RELATIVE_WCF_DIR}';
	var SECURITY_TOKEN = '{@SECURITY_TOKEN}';
	//]]>
</script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/jquery.min.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/3rdParty/jquery-ui.min.js"></script>
<script type="text/javascript" src="{@RELATIVE_WCF_DIR}js/WCF.js"></script>
<script type="text/javascript">
	//<![CDATA[
	WCF.User.init({@$__wcf->user->userID}, '{@$__wcf->user->username|encodejs}');
	//]]>
</script>
