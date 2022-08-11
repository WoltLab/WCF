<?php
// @codingStandardsIgnoreFile

/**
 * Helper script to migrate icons used in WoltLab Suite before version 6.0.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

// TODO
//#!/usr/bin/env php
if (\PHP_SAPI !== 'cli') {
    //exit;
}

// JSON Structure:
// [key: string]: {
//   name: string;
//   type: "brand" | "regular" | "solid";
// }
$json = <<<JSON
{"500px":{"name":"500px","type":"brand"},"address-book-o":{"name":"address-book","type":"regular"},"address-card-o":{"name":"address-card","type":"regular"},"adn":{"name":"adn","type":"brand"},"amazon":{"name":"amazon","type":"brand"},"android":{"name":"android","type":"brand"},"angellist":{"name":"angellist","type":"brand"},"apple":{"name":"apple","type":"brand"},"area-chart":{"name":"chart-area","type":"solid"},"arrow-circle-o-down":{"name":"circle-down","type":"regular"},"arrow-circle-o-left":{"name":"circle-left","type":"regular"},"arrow-circle-o-right":{"name":"circle-right","type":"regular"},"arrow-circle-o-up":{"name":"circle-up","type":"regular"},"arrows":{"name":"up-down-left-right","type":"solid"},"arrows-alt":{"name":"maximize","type":"solid"},"arrows-h":{"name":"left-right","type":"solid"},"arrows-v":{"name":"up-down","type":"solid"},"asl-interpreting":{"name":"hands-asl-interpreting","type":"solid"},"automobile":{"name":"car","type":"solid"},"bandcamp":{"name":"bandcamp","type":"brand"},"bank":{"name":"building-columns","type":"solid"},"bar-chart":{"name":"chart-column","type":"solid"},"bar-chart-o":{"name":"chart-column","type":"solid"},"bathtub":{"name":"bath","type":"solid"},"battery":{"name":"battery-full","type":"solid"},"battery-0":{"name":"battery-empty","type":"solid"},"battery-1":{"name":"battery-quarter","type":"solid"},"battery-2":{"name":"battery-half","type":"solid"},"battery-3":{"name":"battery-three-quarters","type":"solid"},"battery-4":{"name":"battery-full","type":"solid"},"behance":{"name":"behance","type":"brand"},"behance-square":{"name":"square-behance","type":"brand"},"bell-o":{"name":"bell","type":"regular"},"bell-slash-o":{"name":"bell-slash","type":"regular"},"bitbucket":{"name":"bitbucket","type":"brand"},"bitbucket-square":{"name":"bitbucket","type":"brand"},"bitcoin":{"name":"btc","type":"brand"},"black-tie":{"name":"black-tie","type":"brand"},"bluetooth":{"name":"bluetooth","type":"brand"},"bluetooth-b":{"name":"bluetooth-b","type":"brand"},"bookmark-o":{"name":"bookmark","type":"regular"},"btc":{"name":"btc","type":"brand"},"building-o":{"name":"building","type":"regular"},"buysellads":{"name":"buysellads","type":"brand"},"cab":{"name":"taxi","type":"solid"},"calendar":{"name":"calendar-days","type":"solid"},"calendar-check-o":{"name":"calendar-check","type":"regular"},"calendar-minus-o":{"name":"calendar-minus","type":"regular"},"calendar-o":{"name":"calendar","type":"regular"},"calendar-plus-o":{"name":"calendar-plus","type":"regular"},"calendar-times-o":{"name":"calendar-xmark","type":"regular"},"caret-square-o-down":{"name":"square-caret-down","type":"regular"},"caret-square-o-left":{"name":"square-caret-left","type":"regular"},"caret-square-o-right":{"name":"square-caret-right","type":"regular"},"caret-square-o-up":{"name":"square-caret-up","type":"regular"},"cc":{"name":"closed-captioning","type":"regular"},"cc-amex":{"name":"cc-amex","type":"brand"},"cc-diners-club":{"name":"cc-diners-club","type":"brand"},"cc-discover":{"name":"cc-discover","type":"brand"},"cc-jcb":{"name":"cc-jcb","type":"brand"},"cc-mastercard":{"name":"cc-mastercard","type":"brand"},"cc-paypal":{"name":"cc-paypal","type":"brand"},"cc-stripe":{"name":"cc-stripe","type":"brand"},"cc-visa":{"name":"cc-visa","type":"brand"},"chain":{"name":"link","type":"solid"},"chain-broken":{"name":"link-slash","type":"solid"},"check-circle-o":{"name":"circle-check","type":"regular"},"check-square-o":{"name":"square-check","type":"regular"},"chrome":{"name":"chrome","type":"brand"},"circle-o":{"name":"circle","type":"regular"},"circle-o-notch":{"name":"circle-notch","type":"solid"},"circle-thin":{"name":"circle","type":"regular"},"clipboard":{"name":"paste","type":"solid"},"clock-o":{"name":"clock","type":"regular"},"clone":{"name":"clone","type":"regular"},"close":{"name":"xmark","type":"solid"},"cloud-download":{"name":"cloud-arrow-down","type":"solid"},"cloud-upload":{"name":"cloud-arrow-up","type":"solid"},"cny":{"name":"yen-sign","type":"solid"},"code-fork":{"name":"code-branch","type":"solid"},"codepen":{"name":"codepen","type":"brand"},"codiepie":{"name":"codiepie","type":"brand"},"comment-o":{"name":"comment","type":"regular"},"commenting":{"name":"comment-dots","type":"solid"},"commenting-o":{"name":"comment-dots","type":"regular"},"comments-o":{"name":"comments","type":"regular"},"compass":{"name":"compass","type":"regular"},"compress":{"name":"down-left-and-up-right-to-center","type":"solid"},"connectdevelop":{"name":"connectdevelop","type":"brand"},"contao":{"name":"contao","type":"brand"},"copyright":{"name":"copyright","type":"regular"},"creative-commons":{"name":"creative-commons","type":"brand"},"credit-card":{"name":"credit-card","type":"regular"},"credit-card-alt":{"name":"credit-card","type":"solid"},"css3":{"name":"css3","type":"brand"},"cut":{"name":"scissors","type":"solid"},"cutlery":{"name":"utensils","type":"solid"},"dashboard":{"name":"gauge-high","type":"solid"},"dashcube":{"name":"dashcube","type":"brand"},"deafness":{"name":"ear-deaf","type":"solid"},"dedent":{"name":"outdent","type":"solid"},"delicious":{"name":"delicious","type":"brand"},"deviantart":{"name":"deviantart","type":"brand"},"diamond":{"name":"gem","type":"regular"},"digg":{"name":"digg","type":"brand"},"dollar":{"name":"dollar-sign","type":"solid"},"dot-circle-o":{"name":"circle-dot","type":"regular"},"dribbble":{"name":"dribbble","type":"brand"},"drivers-license":{"name":"id-card","type":"solid"},"drivers-license-o":{"name":"id-card","type":"regular"},"dropbox":{"name":"dropbox","type":"brand"},"drupal":{"name":"drupal","type":"brand"},"edge":{"name":"edge","type":"brand"},"edit":{"name":"pen-to-square","type":"regular"},"eercast":{"name":"sellcast","type":"brand"},"empire":{"name":"empire","type":"brand"},"envelope-o":{"name":"envelope","type":"regular"},"envelope-open-o":{"name":"envelope-open","type":"regular"},"envira":{"name":"envira","type":"brand"},"etsy":{"name":"etsy","type":"brand"},"eur":{"name":"euro-sign","type":"solid"},"euro":{"name":"euro-sign","type":"solid"},"exchange":{"name":"right-left","type":"solid"},"expand":{"name":"up-right-and-down-left-from-center","type":"solid"},"expeditedssl":{"name":"expeditedssl","type":"brand"},"external-link":{"name":"up-right-from-square","type":"solid"},"external-link-square":{"name":"square-up-right","type":"solid"},"eye":{"name":"eye","type":"regular"},"eye-slash":{"name":"eye-slash","type":"regular"},"eyedropper":{"name":"eye-dropper","type":"solid"},"fa":{"name":"font-awesome","type":"brand"},"facebook":{"name":"facebook-f","type":"brand"},"facebook-f":{"name":"facebook-f","type":"brand"},"facebook-official":{"name":"facebook","type":"brand"},"facebook-square":{"name":"square-facebook","type":"brand"},"feed":{"name":"rss","type":"solid"},"file-archive-o":{"name":"file-zipper","type":"regular"},"file-audio-o":{"name":"file-audio","type":"regular"},"file-code-o":{"name":"file-code","type":"regular"},"file-excel-o":{"name":"file-excel","type":"regular"},"file-image-o":{"name":"file-image","type":"regular"},"file-movie-o":{"name":"file-video","type":"regular"},"file-o":{"name":"file","type":"regular"},"file-pdf-o":{"name":"file-pdf","type":"regular"},"file-photo-o":{"name":"file-image","type":"regular"},"file-picture-o":{"name":"file-image","type":"regular"},"file-powerpoint-o":{"name":"file-powerpoint","type":"regular"},"file-sound-o":{"name":"file-audio","type":"regular"},"file-text":{"name":"file-lines","type":"solid"},"file-text-o":{"name":"file-lines","type":"regular"},"file-video-o":{"name":"file-video","type":"regular"},"file-word-o":{"name":"file-word","type":"regular"},"file-zip-o":{"name":"file-zipper","type":"regular"},"files-o":{"name":"copy","type":"regular"},"firefox":{"name":"firefox","type":"brand"},"first-order":{"name":"first-order","type":"brand"},"flag-o":{"name":"flag","type":"regular"},"flash":{"name":"bolt","type":"solid"},"flickr":{"name":"flickr","type":"brand"},"floppy-o":{"name":"floppy-disk","type":"regular"},"folder-o":{"name":"folder","type":"regular"},"folder-open-o":{"name":"folder-open","type":"regular"},"font-awesome":{"name":"font-awesome","type":"brand"},"fonticons":{"name":"fonticons","type":"brand"},"fort-awesome":{"name":"fort-awesome","type":"brand"},"forumbee":{"name":"forumbee","type":"brand"},"foursquare":{"name":"foursquare","type":"brand"},"free-code-camp":{"name":"free-code-camp","type":"brand"},"frown-o":{"name":"face-frown","type":"regular"},"futbol-o":{"name":"futbol","type":"regular"},"gbp":{"name":"sterling-sign","type":"solid"},"ge":{"name":"empire","type":"brand"},"gear":{"name":"gear","type":"solid"},"gears":{"name":"gears","type":"solid"},"get-pocket":{"name":"get-pocket","type":"brand"},"gg":{"name":"gg","type":"brand"},"gg-circle":{"name":"gg-circle","type":"brand"},"git":{"name":"git","type":"brand"},"git-square":{"name":"square-git","type":"brand"},"github":{"name":"github","type":"brand"},"github-alt":{"name":"github-alt","type":"brand"},"github-square":{"name":"square-github","type":"brand"},"gitlab":{"name":"gitlab","type":"brand"},"gittip":{"name":"gratipay","type":"brand"},"glass":{"name":"martini-glass-empty","type":"solid"},"glide":{"name":"glide","type":"brand"},"glide-g":{"name":"glide-g","type":"brand"},"globe":{"name":"earth-americas","type":"solid"},"google":{"name":"google","type":"brand"},"google-plus":{"name":"google-plus-g","type":"brand"},"google-plus-circle":{"name":"google-plus","type":"brand"},"google-plus-official":{"name":"google-plus","type":"brand"},"google-plus-square":{"name":"square-google-plus","type":"brand"},"google-wallet":{"name":"google-wallet","type":"brand"},"gratipay":{"name":"gratipay","type":"brand"},"grav":{"name":"grav","type":"brand"},"group":{"name":"users","type":"solid"},"hacker-news":{"name":"hacker-news","type":"brand"},"hand-grab-o":{"name":"hand-back-fist","type":"regular"},"hand-lizard-o":{"name":"hand-lizard","type":"regular"},"hand-o-down":{"name":"hand-point-down","type":"regular"},"hand-o-left":{"name":"hand-point-left","type":"regular"},"hand-o-right":{"name":"hand-point-right","type":"regular"},"hand-o-up":{"name":"hand-point-up","type":"regular"},"hand-paper-o":{"name":"hand","type":"regular"},"hand-peace-o":{"name":"hand-peace","type":"regular"},"hand-pointer-o":{"name":"hand-pointer","type":"regular"},"hand-rock-o":{"name":"hand-back-fist","type":"regular"},"hand-scissors-o":{"name":"hand-scissors","type":"regular"},"hand-spock-o":{"name":"hand-spock","type":"regular"},"hand-stop-o":{"name":"hand","type":"regular"},"handshake-o":{"name":"handshake","type":"regular"},"hard-of-hearing":{"name":"ear-deaf","type":"solid"},"hdd-o":{"name":"hard-drive","type":"regular"},"header":{"name":"heading","type":"solid"},"heart-o":{"name":"heart","type":"regular"},"home":{"name":"house","type":"solid"},"hospital-o":{"name":"hospital","type":"regular"},"hotel":{"name":"bed","type":"solid"},"hourglass-1":{"name":"hourglass-start","type":"solid"},"hourglass-2":{"name":"hourglass-half","type":"solid"},"hourglass-3":{"name":"hourglass-end","type":"solid"},"hourglass-o":{"name":"hourglass","type":"solid"},"houzz":{"name":"houzz","type":"brand"},"html5":{"name":"html5","type":"brand"},"id-badge":{"name":"id-badge","type":"regular"},"id-card-o":{"name":"id-card","type":"regular"},"ils":{"name":"shekel-sign","type":"solid"},"image":{"name":"image","type":"regular"},"imdb":{"name":"imdb","type":"brand"},"inr":{"name":"indian-rupee-sign","type":"solid"},"instagram":{"name":"instagram","type":"brand"},"institution":{"name":"building-columns","type":"solid"},"internet-explorer":{"name":"internet-explorer","type":"brand"},"intersex":{"name":"mars-and-venus","type":"solid"},"ioxhost":{"name":"ioxhost","type":"brand"},"joomla":{"name":"joomla","type":"brand"},"jpy":{"name":"yen-sign","type":"solid"},"jsfiddle":{"name":"jsfiddle","type":"brand"},"keyboard-o":{"name":"keyboard","type":"regular"},"krw":{"name":"won-sign","type":"solid"},"lastfm":{"name":"lastfm","type":"brand"},"lastfm-square":{"name":"square-lastfm","type":"brand"},"leanpub":{"name":"leanpub","type":"brand"},"legal":{"name":"gavel","type":"solid"},"lemon-o":{"name":"lemon","type":"regular"},"level-down":{"name":"turn-down","type":"solid"},"level-up":{"name":"turn-up","type":"solid"},"life-bouy":{"name":"life-ring","type":"solid"},"life-buoy":{"name":"life-ring","type":"solid"},"life-saver":{"name":"life-ring","type":"solid"},"lightbulb-o":{"name":"lightbulb","type":"regular"},"line-chart":{"name":"chart-line","type":"solid"},"linkedin":{"name":"linkedin-in","type":"brand"},"linkedin-square":{"name":"linkedin","type":"brand"},"linode":{"name":"linode","type":"brand"},"linux":{"name":"linux","type":"brand"},"list-alt":{"name":"rectangle-list","type":"regular"},"long-arrow-down":{"name":"down-long","type":"solid"},"long-arrow-left":{"name":"left-long","type":"solid"},"long-arrow-right":{"name":"right-long","type":"solid"},"long-arrow-up":{"name":"up-long","type":"solid"},"magic":{"name":"wand-magic-sparkles","type":"solid"},"mail-forward":{"name":"share","type":"solid"},"mail-reply":{"name":"reply","type":"solid"},"mail-reply-all":{"name":"reply-all","type":"solid"},"map-marker":{"name":"location-dot","type":"solid"},"map-o":{"name":"map","type":"regular"},"maxcdn":{"name":"maxcdn","type":"brand"},"medium":{"name":"medium","type":"brand"},"meetup":{"name":"meetup","type":"brand"},"meh-o":{"name":"face-meh","type":"regular"},"minus-square-o":{"name":"square-minus","type":"regular"},"mixcloud":{"name":"mixcloud","type":"brand"},"mobile":{"name":"mobile-screen-button","type":"solid"},"mobile-phone":{"name":"mobile-screen-button","type":"solid"},"modx":{"name":"modx","type":"brand"},"money":{"name":"money-bill-1","type":"solid"},"moon-o":{"name":"moon","type":"regular"},"mortar-board":{"name":"graduation-cap","type":"solid"},"navicon":{"name":"bars","type":"solid"},"newspaper-o":{"name":"newspaper","type":"regular"},"object-group":{"name":"object-group","type":"regular"},"object-ungroup":{"name":"object-ungroup","type":"regular"},"odnoklassniki":{"name":"odnoklassniki","type":"brand"},"odnoklassniki-square":{"name":"square-odnoklassniki","type":"brand"},"opencart":{"name":"opencart","type":"brand"},"openid":{"name":"openid","type":"brand"},"opera":{"name":"opera","type":"brand"},"optin-monster":{"name":"optin-monster","type":"brand"},"pagelines":{"name":"pagelines","type":"brand"},"paper-plane-o":{"name":"paper-plane","type":"regular"},"pause-circle-o":{"name":"circle-pause","type":"regular"},"paypal":{"name":"paypal","type":"brand"},"pencil-square":{"name":"square-pen","type":"solid"},"pencil-square-o":{"name":"pen-to-square","type":"regular"},"photo":{"name":"image","type":"regular"},"picture-o":{"name":"image","type":"regular"},"pie-chart":{"name":"chart-pie","type":"solid"},"pied-piper":{"name":"pied-piper","type":"brand"},"pied-piper-alt":{"name":"pied-piper-alt","type":"brand"},"pied-piper-pp":{"name":"pied-piper-pp","type":"brand"},"pinterest":{"name":"pinterest","type":"brand"},"pinterest-p":{"name":"pinterest-p","type":"brand"},"pinterest-square":{"name":"square-pinterest","type":"brand"},"play-circle-o":{"name":"circle-play","type":"regular"},"plus-square-o":{"name":"square-plus","type":"regular"},"product-hunt":{"name":"product-hunt","type":"brand"},"qq":{"name":"qq","type":"brand"},"question-circle-o":{"name":"circle-question","type":"regular"},"quora":{"name":"quora","type":"brand"},"ra":{"name":"rebel","type":"brand"},"ravelry":{"name":"ravelry","type":"brand"},"rebel":{"name":"rebel","type":"brand"},"reddit":{"name":"reddit","type":"brand"},"reddit-alien":{"name":"reddit-alien","type":"brand"},"reddit-square":{"name":"square-reddit","type":"brand"},"refresh":{"name":"arrows-rotate","type":"solid"},"registered":{"name":"registered","type":"regular"},"remove":{"name":"xmark","type":"solid"},"renren":{"name":"renren","type":"brand"},"reorder":{"name":"bars","type":"solid"},"repeat":{"name":"arrow-rotate-right","type":"solid"},"resistance":{"name":"rebel","type":"brand"},"rmb":{"name":"yen-sign","type":"solid"},"rotate-left":{"name":"arrow-rotate-left","type":"solid"},"rotate-right":{"name":"arrow-rotate-right","type":"solid"},"rouble":{"name":"ruble-sign","type":"solid"},"rub":{"name":"ruble-sign","type":"solid"},"ruble":{"name":"ruble-sign","type":"solid"},"rupee":{"name":"indian-rupee-sign","type":"solid"},"s15":{"name":"bath","type":"solid"},"safari":{"name":"safari","type":"brand"},"save":{"name":"floppy-disk","type":"regular"},"scribd":{"name":"scribd","type":"brand"},"sellsy":{"name":"sellsy","type":"brand"},"send":{"name":"paper-plane","type":"solid"},"send-o":{"name":"paper-plane","type":"regular"},"share-square-o":{"name":"share-from-square","type":"solid"},"shekel":{"name":"shekel-sign","type":"solid"},"sheqel":{"name":"shekel-sign","type":"solid"},"shirtsinbulk":{"name":"shirtsinbulk","type":"brand"},"sign-in":{"name":"right-to-bracket","type":"solid"},"sign-out":{"name":"right-from-bracket","type":"solid"},"signing":{"name":"hands","type":"solid"},"simplybuilt":{"name":"simplybuilt","type":"brand"},"skyatlas":{"name":"skyatlas","type":"brand"},"skype":{"name":"skype","type":"brand"},"slack":{"name":"slack","type":"brand"},"slideshare":{"name":"slideshare","type":"brand"},"smile-o":{"name":"face-smile","type":"regular"},"snapchat":{"name":"snapchat","type":"brand"},"snapchat-ghost":{"name":"snapchat","type":"brand"},"snapchat-square":{"name":"square-snapchat","type":"brand"},"snowflake-o":{"name":"snowflake","type":"regular"},"soccer-ball-o":{"name":"futbol","type":"regular"},"sort-alpha-asc":{"name":"arrow-down-a-z","type":"solid"},"sort-alpha-desc":{"name":"arrow-down-z-a","type":"solid"},"sort-amount-asc":{"name":"arrow-down-short-wide","type":"solid"},"sort-amount-desc":{"name":"arrow-down-wide-short","type":"solid"},"sort-asc":{"name":"sort-up","type":"solid"},"sort-desc":{"name":"sort-down","type":"solid"},"sort-numeric-asc":{"name":"arrow-down-1-9","type":"solid"},"sort-numeric-desc":{"name":"arrow-down-9-1","type":"solid"},"soundcloud":{"name":"soundcloud","type":"brand"},"spotify":{"name":"spotify","type":"brand"},"square-o":{"name":"square","type":"regular"},"stack-exchange":{"name":"stack-exchange","type":"brand"},"stack-overflow":{"name":"stack-overflow","type":"brand"},"star-half-empty":{"name":"star-half-stroke","type":"regular"},"star-half-full":{"name":"star-half-stroke","type":"regular"},"star-half-o":{"name":"star-half-stroke","type":"regular"},"star-o":{"name":"star","type":"regular"},"steam":{"name":"steam","type":"brand"},"steam-square":{"name":"square-steam","type":"brand"},"sticky-note-o":{"name":"note-sticky","type":"regular"},"stop-circle-o":{"name":"circle-stop","type":"regular"},"stumbleupon":{"name":"stumbleupon","type":"brand"},"stumbleupon-circle":{"name":"stumbleupon-circle","type":"brand"},"sun-o":{"name":"sun","type":"regular"},"superpowers":{"name":"superpowers","type":"brand"},"support":{"name":"life-ring","type":"solid"},"tablet":{"name":"tablet-screen-button","type":"solid"},"tachometer":{"name":"gauge-high","type":"solid"},"tasks":{"name":"bars-progress","type":"solid"},"telegram":{"name":"telegram","type":"brand"},"television":{"name":"tv","type":"solid"},"tencent-weibo":{"name":"tencent-weibo","type":"brand"},"themeisle":{"name":"themeisle","type":"brand"},"thermometer":{"name":"temperature-full","type":"solid"},"thermometer-0":{"name":"temperature-empty","type":"solid"},"thermometer-1":{"name":"temperature-quarter","type":"solid"},"thermometer-2":{"name":"temperature-half","type":"solid"},"thermometer-3":{"name":"temperature-three-quarters","type":"solid"},"thermometer-4":{"name":"temperature-full","type":"solid"},"thumb-tack":{"name":"thumbtack","type":"solid"},"thumbs-o-down":{"name":"thumbs-down","type":"regular"},"thumbs-o-up":{"name":"thumbs-up","type":"regular"},"times-circle-o":{"name":"circle-xmark","type":"regular"},"times-rectangle":{"name":"rectangle-xmark","type":"solid"},"times-rectangle-o":{"name":"rectangle-xmark","type":"regular"},"toggle-down":{"name":"square-caret-down","type":"regular"},"toggle-left":{"name":"square-caret-left","type":"regular"},"toggle-right":{"name":"square-caret-right","type":"regular"},"toggle-up":{"name":"square-caret-up","type":"regular"},"transgender":{"name":"mars-and-venus","type":"solid"},"transgender-alt":{"name":"transgender","type":"solid"},"trash":{"name":"trash-can","type":"solid"},"trash-o":{"name":"trash-can","type":"regular"},"trello":{"name":"trello","type":"brand"},"try":{"name":"turkish-lira-sign","type":"solid"},"tumblr":{"name":"tumblr","type":"brand"},"tumblr-square":{"name":"square-tumblr","type":"brand"},"turkish-lira":{"name":"turkish-lira-sign","type":"solid"},"twitch":{"name":"twitch","type":"brand"},"twitter":{"name":"twitter","type":"brand"},"twitter-square":{"name":"square-twitter","type":"brand"},"unlink":{"name":"link-slash","type":"solid"},"unlock-alt":{"name":"unlock","type":"solid"},"unsorted":{"name":"sort","type":"solid"},"usb":{"name":"usb","type":"brand"},"usd":{"name":"dollar-sign","type":"solid"},"user-circle-o":{"name":"circle-user","type":"regular"},"user-o":{"name":"user","type":"regular"},"vcard":{"name":"address-card","type":"solid"},"vcard-o":{"name":"address-card","type":"regular"},"viacoin":{"name":"viacoin","type":"brand"},"viadeo":{"name":"viadeo","type":"brand"},"viadeo-square":{"name":"square-viadeo","type":"brand"},"video-camera":{"name":"video","type":"solid"},"vimeo":{"name":"vimeo-v","type":"brand"},"vimeo-square":{"name":"square-vimeo","type":"brand"},"vine":{"name":"vine","type":"brand"},"vk":{"name":"vk","type":"brand"},"volume-control-phone":{"name":"phone-volume","type":"solid"},"warning":{"name":"triangle-exclamation","type":"solid"},"wechat":{"name":"weixin","type":"brand"},"weibo":{"name":"weibo","type":"brand"},"weixin":{"name":"weixin","type":"brand"},"whatsapp":{"name":"whatsapp","type":"brand"},"wheelchair-alt":{"name":"accessible-icon","type":"brand"},"wikipedia-w":{"name":"wikipedia-w","type":"brand"},"window-close-o":{"name":"rectangle-xmark","type":"regular"},"window-maximize":{"name":"window-maximize","type":"regular"},"window-restore":{"name":"window-restore","type":"regular"},"windows":{"name":"windows","type":"brand"},"won":{"name":"won-sign","type":"solid"},"wordpress":{"name":"wordpress","type":"brand"},"wpbeginner":{"name":"wpbeginner","type":"brand"},"wpexplorer":{"name":"wpexplorer","type":"brand"},"wpforms":{"name":"wpforms","type":"brand"},"xing":{"name":"xing","type":"brand"},"xing-square":{"name":"square-xing","type":"brand"},"y-combinator":{"name":"y-combinator","type":"brand"},"y-combinator-square":{"name":"hacker-news","type":"brand"},"yahoo":{"name":"yahoo","type":"brand"},"yc":{"name":"y-combinator","type":"brand"},"yc-square":{"name":"hacker-news","type":"brand"},"yelp":{"name":"yelp","type":"brand"},"yen":{"name":"yen-sign","type":"solid"},"yoast":{"name":"yoast","type":"brand"},"youtube":{"name":"youtube","type":"brand"},"youtube-play":{"name":"youtube","type":"brand"},"youtube-square":{"name":"square-youtube","type":"brand"}}
JSON;

$iconShim = json_decode($json, true);
$knownSizes = [16, 24, 32, 48, 64, 96, 128, 144];

function replaceInFiles(string $path): void
{
    //$targetFilenames = ['js', 'tpl', 'ts'];
    $targetFilenames = ['js', 'ts'];

    $directory = new RecursiveDirectoryIterator($path);
    $filter = new RecursiveCallbackFilterIterator(
        $directory,
        function (SplFileInfo $current, string $key, RecursiveDirectoryIterator $iterator) use ($targetFilenames) {
            $filename = $current->getFilename();
            if ($filename === '.' || $filename === '..') {
                return false;
            }

            if ($current->isDir()) {
                return true;
            }

            $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
            return in_array($fileExtension, $targetFilenames);
        }
    );
    $iterator = new RecursiveIteratorIterator($filter);
    /** @var SplFileInfo $fileInfo */
    foreach ($iterator as $fileInfo) {
        replaceIcons($fileInfo->getPathname());
    }
}

function replaceIcons(string $filename): void
{
    global $iconShim, $knownSizes;

    $fileExtension = pathinfo($filename, PATHINFO_EXTENSION);
    $content = file_get_contents($filename);

    $content = preg_replace_callback(
        '~<span class="icon icon(?<size>\d{2,3}) fa-(?<name>[a-zA-Z0-9-]+)"></span>~',
        function (array $matches) use ($fileExtension, $iconShim, $knownSizes): string {
            [
                'name' => $name,
                'size' => $size,
            ] = $matches;

            if (!in_array($size, $knownSizes)) {
                return $matches[0];
            }

            if (isset($iconShim[$name])) {
                [
                    'name' => $newIconName,
                    'type' => $type,
                ] = $iconShim[$name];
            } else {
                // Not all icons are renamed.
                $newIconName = $name;
                $type = 'solid';
            }

            if ($fileExtension === 'tpl') {
                return getNewTemplateIcon($newIconName, $size, $type);
            } else {
                // Brand icons can only be set through the `{icon}` helper.
                if ($type === 'brand') {
                    return $matches[0];
                }

                return getNewJavascriptIcon($newIconName, $size, $type);
            }
        },
        $content,
        -1,
        $count
    );

    if ($count > 0) {
        file_put_contents($filename, $content);
    }
}

function getNewTemplateIcon(string $name, int $size, string $type): string
{
    if ($type === 'regular') {
        return "{icon size={$size} name='{$name}'}";
    }

    return "{icon size={$size} name='{$name}' type='{$type}'}";
}

function getNewJavascriptIcon(string $name, int $size, string $type): string
{
    if ($type === 'regular') {
        return <<<HTML
        <fa-icon size="{$size}" name="{$name}"></fa-icon>
        HTML;
    }

    return <<<HTML
    <fa-icon size="{$size}" name="{$name}" solid></fa-icon>
    HTML;
}


echo "<pre>";

// TODO
replaceInFiles("../com.woltlab.wcf/");
replaceInFiles("../wcfsetup/");
replaceInFiles("../ts/");

echo "Done";
