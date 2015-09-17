<?php

/**
 * @copyright Smart Dealership, 2012
 * @author    Patrick Otto
 * @access    Public
 * @see       Class of getimage control
 */
// Config core integration
define('DS', (PHP_OS == 'Linux' or PHP_OS == 'Darwin') ? '/' : '\\');
define('CURRENT_DIR', dirname(__FILE__) . DS);
define('CACHE_DIR', CURRENT_DIR . 'cache' . DS);
define('CACHE_DEFAULT', CURRENT_DIR . 'default' . DS);

// - - - - - - - - - - - - - - - - - - -
//  DefaultImage
// - - - - - - - - - - - - - - - - - - -

function DefaultImage() {
    $defaultImg = CACHE_DEFAULT . 'default.png';
    $info = getimagesize($defaultImg);
    $fp = fopen($defaultImg, "rb");

    // valid image
    if ($info && $fp) {

        // read image
        $sendImg = DetectImage($defaultImg);
        $thumb = null;

        // cordenates
        $inew_width = (isset($_GET['img_w'])) ? (int) $_GET['img_w'] : 0;
        $auto_height = (isset($_GET['img_h'])) ? (int) $_GET['img_h'] : 0;

        $thumb = SmartResizeImage($defaultImg, $inew_width, $auto_height, null, $info, false);

        // check if image was rendenized
        $return = empty($thumb) ? $sendImg : $thumb;

        // send image
        header('HTTP/1.1 206', true, 206); // caution: HTTP status code 206 use for valid default image car, not change this line, about render that image for all clientes and core SDS
        header('Content-type: image/png');
        imagepng($return);
        imagedestroy($return);

        exit;
    }

    // return invalid send
    header('HTTP/1.1 400 Bad Request');
    die('Error: no image was specified');
}

// - - - - - - - - - - - - - - - - - - -
//  DetectImage
// - - - - - - - - - - - - - - - - - - -

function DetectImage($uri) {

    // get image info
    $info = getimagesize($uri);

    // valid mime type
    if (empty($info['mime']))
        DefaultImage();

    // get mime type
    $mime = $info['mime'];

    // detect image type 'png'
    if (preg_match('/png/im', $mime))
        $send = imagecreatefrompng($uri);

    // detect image type 'jpg or jpeg'
    if (preg_match('/jpg|jpeg/im', $mime))
        $send = imagecreatefromjpeg($uri);

    // detect image type 'gif'
    if (preg_match('/gif/im', $mime))
        $send = imagecreatefromgif($uri);

    // valid new image created
    if (empty($send))
        DefaultImage();

    // callback
    return $send;
}

// - - - - - - - - - - - - - - - - - - -
//  SmartResizeImage
// - - - - - - - - - - - - - - - - - - -

function SmartResizeImage($img, $w, $h, $newfilename, $imgInfo, $save = true) {

    if (empty($newfilename))
        $newfilename = 'default.jpg';

    // check if gd extension is loaded
    if (!extension_loaded('gd') && !extension_loaded('gd2')) {
        trigger_error("gd extension not found in php", E_USER_WARNING);
        return false;
    }

    // get image size info
    switch ($imgInfo[2]) {
        case 1: $im = imagecreatefromgif($img);
            break;
        case 2: $im = imagecreatefromjpeg($img);
            break;
        case 3: $im = imagecreatefrompng($img);
            break;
        default: trigger_error('unsuported file, (png, jpg, gif) ', E_USER_WARNING);
            break;
    }

    // if image dimension is smaller, do not resize
    if (empty($w) && empty($h)):
        $nHeight = (int) $imgInfo[1];
        $nWidth = (int) $imgInfo[0];
    else:

        // yeah, resize it, but keep it proportional
        if (empty($h)) {
            $nWidth = $w;
            $nHeight = $imgInfo[1] * ($w / $imgInfo[0]);
        } elseif (empty($w)) {
            $nHeight = $h;
            $nWidth = $imgInfo[0] * ($h / $imgInfo[1]);
        } else {
            $nHeight = $h;
            $nWidth = $w;
        }

    endif;

    $nWidth = (int) round($nWidth);
    $nHeight = (int) round($nHeight);

    $newImg = imagecreatetruecolor($nWidth, $nHeight);

    // check if this image is png or gif, then set if transparent* 
    if (($imgInfo[2] == 1) OR ($imgInfo[2] == 3)) {
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);
    }

    // resample
    imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);

    if ($save):

        // generate the file, and rename it to $newfilename
        switch ($imgInfo[2]) {
            case 1: imagegif($newImg, $newfilename);
                break;
            case 2: imagejpeg($newImg, $newfilename);
                break;
            case 3: imagepng($newImg, $newfilename);
                break;
            default: trigger_error('image resize fail!', E_USER_WARNING);
                break;
        }

    endif;

    // return
    return $newImg;
}

// - - - - - - - - - - - - - - - - - - -
//  SendImageFromCache
// - - - - - - - - - - - - - - - - - - -

function SendImageFromCache($src) {
    header('Content-type: image/jpeg');
    $send = imagecreatefromjpeg($src);
    imagejpeg($send, null, 90);
    imagedestroy($send);
    exit;
}

// - - - - - - - - - - - - - - - - - - -
//  CreateImageToCache
// - - - - - - - - - - - - - - - - - - -	

function CreateImageToCache($handle, $thumb_w, $thumb_h, $back, $opt, $src) {
	
    $remote_image = DetectImage($handle);

    list($w, $h) = getimagesize($handle);
    if ($h != 0)
        $q = $w / $h;

    // Check image size
    if (getimagesize($handle))
        list($w, $h) = getimagesize($handle);
    if ($h != 0)
        $q = $w / $h;

    // Generate size of thumb
    if (($thumb_w == -1) or ($thumb_h == -1)):
        if (($thumb_w == -1) and ($thumb_h == -1)):
            $thumb_w = $_GET['img_w'];
            $thumb_h = $_GET['img_h'];
        else:
            if (($thumb_h != -1) and ($thumb_w == -1))
                $thumb_w = ((int) $thumb_h * $q);
            if (($thumb_h == -1) and ($thumb_w != -1))
                $thumb_h = ((int) $thumb_w / $q);
        endif;
    endif;

    // Valid size of the thumb
    if ($thumb_w > 1000)
        $thumb_w = 650;
    if ($thumb_w < 10)
        $thumb_w = 10;
    if ($thumb_h > 1000)
        $thumb_h = 650;
    if ($thumb_h < 10)
        $thumb_h = 10;

    // Gera thumb
    $thumb = imagecreatetruecolor($thumb_w, $thumb_h);
    $f = imagecolorallocate($thumb, $back[0], $back[1], $back[2]);
    imagefill($thumb, 0, 0, $f);

    // Aplica Imagem sobre thumb
    imagecopyresampled($thumb, $remote_image, 0, 0, 0, 0, $thumb_w, $thumb_h, $w, $h);

    // Case Corsia
    if ($opt == '_corsia_'):

        //imagefilter($thumb, IMG_FILTER_BRIGHTNESS, 80);
        $src_t = CACHE_DEFAULT . 'em-producao.png';
        list($src_w, $src_h) = getimagesize($src_t);
        $res = imagecreatefrompng($src_t);
        imagealphablending($res, true);
        imagecopymerge($thumb, $res, ($thumb_w / 2 - $src_w / 2), ($thumb_h - $src_h) - 3, 0, 0, $src_w, $src_h, 100);
        imagedestroy($res);
    endif;

    // Case Reservado
    if ($opt == '_reservado_'):

        $src_t = CACHE_DEFAULT . 'reservado.png';
        list($src_w, $src_h) = getimagesize($src_t);
        $res = imagecreatefrompng($src_t);
        imagealphablending($res, true);
        imagecopymerge($thumb, $res, ($thumb_w / 2 - $src_w / 2), ($thumb_h - $src_h) - 3, 0, 0, $src_w, $src_h, 100);
        imagedestroy($res);
    endif;

    // Case Vendido
    if ($opt == '_vendido_'):

        $src_t = CACHE_DEFAULT . 'vendido.png';
        list($src_w, $src_h) = getimagesize($src_t);
        $res = imagecreatefrompng($src_t);
        imagealphablending($res, true);
        imagecopymerge($thumb, $res, ($thumb_w / 2 - $src_w / 2), ($thumb_h - $src_h) - 3, 0, 0, $src_w, $src_h, 100);
        imagedestroy($res);
    endif;

    // Armazena a imagem localmente
    imagejpeg($thumb, $src, 90);

    // Envia a imagem
    header('Content-type: image/jpeg');
    imagejpeg($thumb, null, 90);
    imagedestroy($thumb);
    imagedestroy($remote_image);
    exit;
}

// - - - - - - - - - - - - - - - - - - -
//  CreateImageToCache
// - - - - - - - - - - - - - - - - - - -	

function FileCacheIsLive($src, $TTL) {
    $time = time();
    $create = filectime($src);

    if (!$create)
        return false;
		
    $diff = (int) ($time - $create);
	
    if ($TTL > $diff)
        return true;
		
    if (file_exists($src))
        unlink($src);
    return false;
}

// - - - - - - - - - - - - - - - - - - -
//  CheckRemoteFile  /!\ Any Remote File /!\
// - - - - - - - - - - - - - - - - - - -

function CheckRemoteFile($url) {

    // valid URL
    if (!preg_match('/http[s]?:\/\//m', $url))
        return false;

    // get url
    $ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_NOBODY, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
	
    // get response
    $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	// caso return 0, use alternative response code
	if(!$response_code)
		$response_code = get_headers($url, 1);
	
	// valid header and format, any number
    if(is_array($response_code))
		$response_code = (int) preg_replace('/http\/\d[.]\d\s\s?([0-9]{3}).*/im','$1',$response_code[0]);
			
    // check http status
    if (preg_match('/^200|304|301|300$/m', $response_code)) // caution, HTTP status Code 206/302 use do render default image for all clients and Core Smart Dealer
        return true;
    return false;
}

?>