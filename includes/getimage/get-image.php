<?php

/**
 * @copyright Smart Dealership, 2012
 * @author    Patrick Otto
 * @access    Public
 * @return    image
 * @uses      local dinamic image cache, core integration
 * @param     $m char, model:requerid  ex: DBR1820
 * @param     $c char, color:requerid  ex: NR8
 * @param     $o char, owner:requerid  ex: prima, prima_via, via_porto
 * @param     $i char, color:requerid  ex: 3FAHP0JAXAR397388 (used vehicles)
 * @param     $e char, sequence + extension:requerid  ex: 1.jpg (used vehicles)
 * @param     $img_bg image background ex: 255,255,255
 * @param     $img_w image size width  ex: 300 auto
 * @param     $img_h image size height ex: 200 auto
 * @param     $img_q image qualitity   ex: 90 
 *
 * @example {model}/{color}/{owner}/{width}/{sequence}.{extension}              
 * @example {vehicle_id}/{owner}/{width}/{sequence}.{extension} (used vehicles)  
 * 
 * @example 345OC31/PW3/prima/500/.jpg
 * @example 3FAHP0JAXAR397388_01/prima/500/1.jpg
 */
$back = array(255, 255, 255);
$validParams = array_flip(array('img_bg', 'img_q', 'img_h'));
$opt = 'default';
$ttl = 1800;
$ext = '.cache';
$cache = true;
$defaultQuality = 78;
$validRemote = false;
$filterImage = false;

// request width
$thumb_w = !empty($_GET['img_w']) ? (int) $_GET['img_w'] : -1;

// request height
$thumb_h = !empty($_GET['img_h']) ? (int) $_GET['img_h'] : -1;

// request quality of image
$quality = !empty($_GET['img_q']) ? (int) $_GET['img_q'] : $defaultQuality;

// include libs
require('includes/functions.php');

// valid mandatory params
if ((empty($_REQUEST['m']) or empty($_REQUEST['c']) or empty($_GET['o'])) and ( empty($_GET['i'])))
    DefaultImage();

// change background
if (isset($_GET['img_bg']) and count($a = explode(',', $_GET['img_bg'])) == 3)
    $back = $a;

if (in_array($b = filter_input(INPUT_GET, 'img_bg'), array('transparent')))
    $back = $b;

// mount rgb matrix
if (is_array($b))
    foreach ($back as $k => $v):
        if ($v > 255)
            $v = 255;
        if ($v < 0)
            $v = 0;
        $back[$k] = (int) $v;
    endforeach;

if (isset($_GET['reservado']))
    $opt = '_reservado_';

if (isset($_GET['corsia']))
    $opt = '_corsia_';

if (isset($_GET['vendido']))
    $opt = '_vendido_';

// - - - - - - - - - - - - - - - - - - -
//  Request image in WS
// - - - - - - - - - - - - - - - - - - -

if ($rw = (!empty($_REQUEST['m']) and ! empty($_REQUEST['c'])) or ( isset($_REQUEST['i']) and strstr($i = $_REQUEST['i'], '/'))):

    if (isset($i))
        $i = explode('/', preg_replace('/[.](jpg|png)$/', '', $i));

    // request param, pre sql injection tratament, remove html and limit string code
    $model = ($rw) ? substr(addslashes(trim(strip_tags(preg_replace('/\s/m', '', $_REQUEST['m'])))), 0, 20) : $i[0];
    $color = ($rw) ? substr(addslashes(trim(strip_tags(preg_replace('/\s/m', '', $_REQUEST['c'])))), 0, 20) : $i[1];
    $owner = empty($_REQUEST['o']) ? '' : substr(addslashes(strip_tags(trim(preg_replace('/\s/m', '', $_REQUEST['o'])))), 0, 20);
    $index = max((int) filter_input(INPUT_GET, 'e', FILTER_SANITIZE_NUMBER_INT), 1);

    // mount file name cache
    $src = CACHE_DIR . sha1(CACHE_DIR . $model . $color . $owner . $index . $thumb_w . $thumb_h . $quality . $opt . ((!empty($_GET['img_bg']) and count(explode(',', $_GET['img_bg'])) == 3) ? $_GET['img_bg'] : serialize($back))) . $ext;
    $queryString = http_build_query(array_intersect_key(filter_input_array(INPUT_GET), $validParams));

    // mount url core integration
    $handle = 'https://core.smartdealer.app/img/' . implode('/', array(urlencode($model), urlencode($color), $owner, $thumb_w, $index)) . '.jpg?' . $queryString;

    if ($thumb_h != -1)
        $handle .= '&img_h=' . $thumb_h;

    if (isset($_GET['img_bg']) and count(explode(',', $_GET['img_bg'])) == 3)
        $handle .= '&img_bg=' . $_GET['img_bg'];

    // check if file exists
    if ($cache && file_exists($src))
        if (FileCacheIsLive($src, $ttl))
            SendImageFromCache($src);

    if ($validRemote and ! CheckRemoteFile($handle))
        DefaultImage();

    CreateImageToCache($handle, $thumb_w, $thumb_h, $back, $opt, $src);

elseif (isset($_GET['i']) and isset($_GET['o'])):

    // request param, pre sql injection tratament, remove html and limit string code
    $owner = empty($_GET['o']) ? null : substr(addslashes(strip_tags(trim(preg_replace('/\s/m', '', $_GET['o'])))), 0, 20);
    $image = preg_replace('/\.[a-z]+$/i', '', $_GET['i']);
    $index = max((int) filter_input(INPUT_GET, 'e', FILTER_SANITIZE_NUMBER_INT), 1);

    // mount file name cache
    $src = CACHE_DIR . sha1(CACHE_DIR . $image . $owner . $index . $thumb_w . $thumb_h . $quality . $opt . ((!empty($_GET['img_bg']) and count(explode(',', $_GET['img_bg'])) == 3) ? $_GET['img_bg'] : '255,255,255')) . $ext;

    // mount url core integration
    $handle = 'https://core.smartdealer.app/img/' . urlencode($image) . '/' . $owner . '/' . $thumb_w . '/' . $index . '.jpg';

    // check if file exists
    if ($cache && file_exists($src))
        if (FileCacheIsLive($src, $ttl))
            SendImageFromCache($src);

    if ($validRemote and ! CheckRemoteFile($handle))
        DefaultImage();

    CreateImageToCache($handle, $thumb_w, $thumb_h, $back, $opt, $src);
else:
    // invalid request, return default image 
    DefaultImage();
endif;
?>