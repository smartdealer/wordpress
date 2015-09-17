<?php

/**
 * @copyright Smart Dealership, 2012
 * @author    Patrick Otto
 * @access    Public
 * @return    image
 * @version	  1.0.1
 * @uses      local dinamic image cache, core integration
 * @param     $m char, model:requerid  ex: DBR1820
 * @param     $c char, color:requerid  ex: NR8
 * @param     $o char, owner:requerid  ex: prima, prima_via, via_porto
 * @param     $i char, color:requerid  ex: 3FAHP0JAXAR397388_01 (used vehicles id)
 * @param     $e char, sequence + extension:requerid  ex: 1.jpg (used vehicles)
 * @param     $img_bg image background ex: 255,255,255
 * @param     $img_w image size width  ex: 300 auto
 * @param     $img_h image size height ex: 200 auto
 * @param     $img_q image qualitity   ex: 90 
 *
 * @example [getimage_url]/{model}/{color}/{owner}/{width}/{sequence}.{extension}              
 * @example [getimage_url]/{id_veiculo}/{owner}/{width}/{sequence}.{extension} (used vehicles)  
 * 
 * @tutorial 345OC31/PW3/prima/500/.jpg			  // novos
 * @tutorial 3FAHP0JAXAR397388_01/prima/500/1.jpg // usados
 */
// load module methods
require('./image.class.php');

// valid mandatory params
if ((empty($_REQUEST['m']) or empty($_REQUEST['c']) or empty($_GET['o'])) and (empty($_GET['i'])))
    DefaultImage();

// Default options
$back = array(255, 255, 255);
$opt = 'default';
$ttl = 600; //84600 
$ext = '.cache';

// change background
if (isset($_GET['img_bg']) and count(explode(',', $_GET['img_bg'])) == 3)
    $back = explode(',', $_GET['img_bg']);

// mount rgb matrix
foreach ($back as $k => $v):
    if ($v > 255)
        $v = 255;
    if ($v < 0)
        $v = 0;
    $back[$k] = (int) $v;
endforeach;

// request width
$thumb_w = !empty($_GET['img_w']) ? (int) $_GET['img_w'] : -1;

// request height
$thumb_h = !empty($_GET['img_h']) ? (int) $_GET['img_h'] : -1;

// request quality of image
$quality = !empty($_GET['img_q']) ? (int) $_GET['img_q'] : 90;

if (isset($_GET['reservado']))
    $opt = '_reservado_';

if (isset($_GET['corsia']))
    $opt = '_corsia_';

if (isset($_GET['vendido']))
    $opt = '_vendido_';

// - - - - - - - - - - - - - - - - - - -
//  Request image in WS
// - - - - - - - - - - - - - - - - - - -

if (isset($_REQUEST['m']) and isset($_REQUEST['c'])):

    // request param, pre sql injection tratament, remove html and limit string code
    $model = substr(addslashes(trim(strip_tags(preg_replace('/\s/m', '', $_REQUEST['m'])))), 0, 20);
    $color = substr(addslashes(trim(strip_tags(preg_replace('/\s/m', '', $_REQUEST['c'])))), 0, 20);
    $owner = empty($_REQUEST['o']) ? null : substr(addslashes(strip_tags(trim(preg_replace('/\s/m', '', $_REQUEST['o'])))), 0, 20);

    // mount file name cache
    $src = CACHE_DIR . sha1(CACHE_DIR . $model . $color . $owner . $thumb_w . $thumb_h . $quality . $opt . ((!empty($_GET['img_bg']) and count(explode(',', $_GET['img_bg'])) == 3) ? $_GET['img_bg'] : '255,255,255')) . $ext;

    // mount url core integration
    $handle = 'http://core.smartdealer.com.br/webservice/get-image.php?m=' . $model . '&c=' . $color;

    // optional params
    if (!empty($owner))
        $handle .= '&o=' . $owner;

    if ($thumb_w != -1)
        $handle .= '&img_w=' . $thumb_w;

    if ($thumb_h != -1)
        $handle .= '&img_h=' . $thumb_h;

    if (isset($_GET['img_bg']) and count(explode(',', $_GET['img_bg'])) == 3)
        $handle .= '&img_bg=' . $_GET['img_bg'];

    if (!CheckRemoteFile($handle))
        DefaultImage();

    // check if file exists
    if (file_exists($src))
        if (FileCacheIsLive($src, $ttl))
            SendImageFromCache($src);
    CreateImageToCache($handle, $thumb_w, $thumb_h, $back, $opt, $src);
elseif (isset($_REQUEST['i']) and isset($_REQUEST['o'])):

    // request param, pre sql injection tratament, remove html and limit string code
    $owner = empty($_REQUEST['o']) ? null : substr(addslashes(strip_tags(trim(preg_replace('/\s/m', '', $_REQUEST['o'])))), 0, 20);
    $image = $_REQUEST['i'].'_'.(empty($_REQUEST['e']) ? '1.jpg' : $_REQUEST['e']);

    // mount file name cache
    $src = CACHE_DIR . sha1(CACHE_DIR . $image . $owner . $thumb_w . $thumb_h . $quality . $opt . ((!empty($_GET['img_bg']) and count(explode(',', $_GET['img_bg'])) == 3) ? $_GET['img_bg'] : '255,255,255')) . $ext;
	
	// match image totals
    $totImgs = FindTotalImage('https://' . preg_replace('/[^A-Za-z]/im','',$owner) . '.smartdealer.com.br/numeor/uploads/usados/?list_images='.$_REQUEST['i']);
	$indImgs = ((empty($_REQUEST['e'])) ? 0 : (int) preg_replace('/[^\d]/','',$_REQUEST['e']) - 1);
	
	if(empty($totImgs) or empty($totImgs->images[$indImgs]))
		DefaultImage();
	
    // mount url core integration
    $handle = 'https://' . preg_replace('/[^A-Za-z]/im','',$owner) . '.smartdealer.com.br/numeor/uploads/usados/'.$totImgs->images[$indImgs];

    if (!CheckRemoteFile($handle))
        DefaultImage();
		
    // check if file exists
    if (file_exists($src))
        if (FileCacheIsLive($src, $ttl))
            SendImageFromCache($src);
    CreateImageToCache($handle, $thumb_w, $thumb_h, $back, $opt, $src);
else:
    // invalid request, return default image 
    DefaultImage();
endif;
?>