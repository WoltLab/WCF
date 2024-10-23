<?php

namespace wcf\data;

/**
 * Every database object action providing images for .wcfImageViewer() must implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2
 */
interface IImageViewerAction
{
    /**
     * Validates parameters to load the next images.
     */
    public function validateLoadNextImages();

    /**
     * Returns a list of images, array indices given for 'images' are discarded (series and series 'url' are optional).
     * The first response (offset = 0) must return the total number of images.
     *
     * Each requests contains three parameters:
     *  - offset: number of already loaded image
     *  - maximumHeight: image provided in 'url' must be as close as possible to this value
     *  - maximumWidth: see above
     *  - targetImageID: preload all images which are before the target image id as well as the next X images (optional, always present but might be '0')
     *
     * Each image can specify a link which should not point to the image itself, instead it should provide a viewable
     * page directly related to the image (e.g. photo page). The 'fullURL' parameter is optional and results in the
     * link "View original image" and should lead to the original exceeding lager than the image specified with 'url'.
     *
     * Expected return value:
     * array(
     *  'images' => array(
     *      array(
     *          'image' => array(
     *              'height' => 768,
     *              [ 'fullURL' => 'http://example.com/path/to/full/image.png', ]
     *              [ 'link' => 'http://example.com/index.php/123-MyImage/', ]
     *              'title' => 'My first picture',
     *              'url' => 'http://example.com/path/to/large/image.png',
     *              'width' => 1024
     *          ),
     *          'thumbnail' => array(
     *              'height' => 148,
     *              'url' => 'http://example.com/path/to/thumbnail.png',
     *              'width' => 148
     *          ),
     *          [ 'series' => array(
     *              'title' => 'My image series,
     *              [ 'link' => 'http://example.com/link/to/image/series/ ]
     *          ), ]
     *          'user' => array(
     *              'avatarURL' => 'http://link/to/avatar.png',
     *              'link' => 'http://example.com/index.php/123-FooBar/',
     *              'username' => 'FooBar'
     *          )
     *      ),
     *      // ...
     *  ),
     *  [ 'items' => 123 ]
     * )
     *
     * @return  array
     */
    public function loadNextImages();
}
