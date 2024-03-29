<?php

namespace wcf\data\user\avatar;

use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Represents a default avatar.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DefaultAvatar implements IUserAvatar, ISafeFormatAvatar
{
    /**
     * image size
     * @var int
     */
    public $size = UserAvatar::AVATAR_SIZE;

    /**
     * content of the `src` attribute
     * @var string
     */
    protected $src = '';

    /**
     * DefaultAvatar constructor.
     *
     * @param string $username username for use with the 'initials' avatar type
     */
    public function __construct($username = '')
    {
        if (\defined('AVATAR_DEFAULT_TYPE') && AVATAR_DEFAULT_TYPE === 'initials' && !empty($username)) {
            $words = \explode(' ', $username);
            $count = \count($words);
            if ($count > 1) {
                // combine the first character of each the first and the last word
                $text = \mb_strtoupper(\mb_substr($words[0], 0, 1) . \mb_substr($words[$count - 1], 0, 1));
            } else {
                // use the first two characters
                $text = \mb_strtoupper(\mb_substr($username, 0, 2));
            }

            $text = \htmlspecialchars($text, \ENT_XML1, 'UTF-8');

            $backgroundColor = \substr(\sha1($username), 0, 6);

            $perceptiveLuminance = $this->getPerceptiveLuminance(
                \hexdec($backgroundColor[0] . $backgroundColor[1]),
                \hexdec($backgroundColor[2] . $backgroundColor[3]),
                \hexdec($backgroundColor[4] . $backgroundColor[5])
            );

            $textColor = ($perceptiveLuminance < 0.5) ? '0 0 0' : '255 255 255';

            // the <path> is basically a shorter version of a <rect>
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="128" height="128"><path fill="#{$backgroundColor}" d="M0 0h16v16H0z"/><text x="8" y="8" fill="rgba({$textColor} / 0.89)" text-anchor="middle" dy=".3em" font-family="Arial" font-size="7">{$text}</text></svg>
SVG;

            $this->src = "data:image/svg+xml;base64," . \base64_encode($svg);
        } else {
            $this->src = WCF::getPath() . 'images/avatars/avatar-default.svg';
        }
    }

    /**
     * @inheritDoc
     */
    public function getSafeURL(?int $size = null): string
    {
        return WCF::getPath() . 'images/avatars/avatar-default.png';
    }

    /**
     * @inheritDoc
     */
    public function getSafeImageTag(?int $size = null): string
    {
        return '<img src="' . StringUtil::encodeHTML($this->getSafeURL($size)) . '" width="' . $size . '" height="' . $size . '" alt="" class="userAvatarImage">';
    }

    /**
     * @inheritDoc
     */
    public function getURL($size = null)
    {
        return $this->src;
    }

    /**
     * @inheritDoc
     */
    public function getImageTag($size = null)
    {
        if ($size === null) {
            $size = $this->size;
        }

        return '<img src="' . StringUtil::encodeHTML($this->getURL($size)) . '" width="' . $size . '" height="' . $size . '" alt="" class="userAvatarImage">';
    }

    /**
     * @inheritDoc
     */
    public function getWidth()
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function getHeight()
    {
        return $this->size;
    }

    /**
     * Returns the perceived luminance of the given color.
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return      float           luminance expressed in a float in the range of 0 and 1
     */
    protected function getPerceptiveLuminance($r, $g, $b)
    {
        return 1 - (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
    }
}
