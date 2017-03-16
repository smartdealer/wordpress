<?php

/**
 * @copyright Smart Dealership, 2012 - 2015
 * @author    Patrick Otto
 * @access    Public
 * @see       Class of getimage control
 */
// Config core integration
define('DS', (PHP_OS == 'Linux' or PHP_OS == 'Darwin') ? '/' : '\\');
define('CURRENT_DIR', dirname(__FILE__) . DS . '..' . DS);
define('CACHE_DIR', CURRENT_DIR . 'cache' . DS);
define('CACHE_DEFAULT', CURRENT_DIR . 'default' . DS);

// - - - - - - - - - - - - - - - - - - -
//  DefaultImage
// - - - - - - - - - - - - - - - - - - -

function DefaultImage() {

    global $quality, $thumb_h, $thumb_w;

    $defaultImg = CACHE_DEFAULT . 'default.jpg';

    $thumb_h = ($thumb_h == -1) ? 0 : $thumb_h;
    $thumb_w = ($thumb_w == -1) ? 0 : $thumb_w;

    // valid image
    if (file_exists($defaultImg)) {

        // read image
        $sendImg = DetectImage($defaultImg, 1);

        $info = array(
            imagesx($sendImg),
            imagesy($sendImg),
            IMAGETYPE_JPEG
        );

        // create thumb
        $thumb = SmartResizeImage($defaultImg, $thumb_w, $thumb_h, null, $info, false);

        // check if image was rendenized
        $return = ($thumb) ? $thumb : $sendImg;

        // valid quality
        $a = (min(9, $quality / 10) - 9);
        $quality = ($a >= 0 && $a <= 9) ? $a : 5;

        // send image
        header('Content-type: image/jpg');
        header('Cache-Control: public');
        imagepng($return, null, $quality);
        imagedestroy($return);

        exit(0);
    }

    // return invalid send
    header('HTTP/1.1 400 Bad Request');
    die('Error: no image was specified');
}

// - - - - - - - - - - - - - - - - - - -
//  DetectImage
// - - - - - - - - - - - - - - - - - - -

function DetectImage($uri, $local = 0) {

    global $filterImage;

    if ($filterImage) {

        $exif_types = array(
            IMAGETYPE_GIF => 'image/gif',
            IMAGETYPE_JPEG => 'image/jpeg',
            IMAGETYPE_PNG => 'image/png'
        );

        $use_exif = function_exists('exif_imagetype');

        // get image info
        $mime = ($use_exif && ($a = @exif_imagetype($uri)) && isset($exif_types[$a])) ? $exif_types[$a] : (($a = @getimagesize($uri)) ? $a['mime'] : null);

        // valid mime type
        if (empty($mime) or ! preg_match('/(jpeg|jpg|gif|png)$/', $mime))
            DefaultImage();

        // detect image type 'png'
        if (image_type_to_mime_type(IMAGETYPE_PNG) === $mime)
            $send = imagecreatefrompng($uri);

        // detect image type 'jpeg'
        if (image_type_to_mime_type(IMAGETYPE_JPEG) === $mime)
            $send = imagecreatefromjpeg($uri);

        // detect image type 'gif'
        if (image_type_to_mime_type(IMAGETYPE_GIF) === $mime)
            $send = imagecreatefromgif($uri);
    }

    // detect image from string
    if ($local)
        $send = imagecreatefromstring(file_get_contents($uri));
    elseif (empty($send) && ($content = curl_file_get_contents($uri)))
        $send = imagecreatefromstring($content['content']);
    
    // valid new image created
    if (empty($send) or ! is_resource($send))
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
    if (($imgInfo[2] == 1) OR ( $imgInfo[2] == 3)) {
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefilledrectangle($newImg, 0, 0, $nWidth, $nHeight, $transparent);

        // resize and resample
        imagecopyresampled($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);
    } else {

        // resize
        imagecopyresized($newImg, $im, 0, 0, 0, 0, $nWidth, $nHeight, $imgInfo[0], $imgInfo[1]);
    }

    if ($save):

        // generate the file, and rename it to $newfilename
        switch ($imgInfo[2]) {
            case 1: imagegif($newImg, $newfilename);
                break;
            case 2: imagejpeg($newImg, $newfilename, 100);
                break;
            case 3: imagepng($newImg, $newfilename, 0);
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

function SendImageFromCache($src, $cached = true) {

    // set header
    header('Content-type: image/jpg');
    header('Cache-Control: public');

    // set cache signal
    header('Sd-signature: ' . ($cached ? 'Cached Image' : 'Created Image'));

    // gzhandler
    die(ob_gzhandler(file_get_contents($src), 9));
}

// - - - - - - - - - - - - - - - - - - -
//  CreateImageToCache
// - - - - - - - - - - - - - - - - - - -	

function CreateImageToCache($handle, $thumb_w, $thumb_h, $back, $opt, $src) {

    global $quality;

    $remote_image = DetectImage($handle);

    $w = imagesx($remote_image);
    $h = imagesy($remote_image);

    if ($h != 0)
        $q = $w / $h;

    // Generate size of thumb
    if (($thumb_w == -1) or ( $thumb_h == -1)):
        if (($thumb_w == -1) and ( $thumb_h == -1)):
            $thumb_w = $_GET['img_w'];
            $thumb_h = $_GET['img_h'];
        else:
            if (($thumb_h != -1) and ( $thumb_w == -1))
                $thumb_w = ((int) $thumb_h * $q);
            if (($thumb_h == -1) and ( $thumb_w != -1))
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

    // create thumb
    $thumb = imagecreatetruecolor($thumb_w, $thumb_h);

    // fill color
    if (is_string($back) && stristr($back, 'transparent')) {
        imagealphablending($thumb, true);
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 255, 255, 255, 127));
    } else {
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, $back[0], $back[1], $back[2]));
    }

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
    imagejpeg($thumb, $src, $quality);

    // Envia a imagem
    SendImageFromCache($src, 0);
}

// - - - - - - - - - - - - - - - - - - -
//  CreateImageToCache
// - - - - - - - - - - - - - - - - - - -	

function FileCacheIsLive($src, $TTL) {

    $time = time();
    $create = filemtime($src);

    if (!$create)
        return false;

    $diff = abs($time - $create);

    // return
    return ($diff < $TTL);
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
    if (!$response_code)
        $response_code = get_headers($url, 1);

    // valid header and format, any number
    if (is_array($response_code))
        $response_code = (int) preg_replace('/http\/\d[.]\d\s\s?([0-9]{3}).*/im', '$1', $response_code[0]);

    // check http status
    if (preg_match('/^200|304|301|300$/m', $response_code)) // caution, HTTP status Code 206/302 use do render default image for all clients and Core Smart Dealer
        return true;
    return false;
}

// - - - - - - - - - - - - - - - - - - -
//  curl_file_get_contents
// - - - - - - - - - - - - - - - - - - -	

function curl_file_get_contents($url) {

    // set address
    $curl = curl_init(trim($url, ' ?'));

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
    curl_setopt($curl, CURLOPT_FAILONERROR, TRUE);
    curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($curl, CURLOPT_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_VERBOSE, TRUE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

    // request content
    $content = curl_exec_follow($curl);

    $a = array(
        'type' => curl_getinfo($curl, CURLINFO_CONTENT_TYPE),
        'content' => $content,
        'status' => curl_getinfo($curl, CURLINFO_HTTP_CODE)
    );

    // close
    curl_close($curl);

    // return
    return (preg_match('/^200|304|301|300$/m', $a['status']) && preg_match('/jpg|png|jpeg|gif$/i', $a['type'])) ? $a : null;
}

// - - - - - - - - - - - - - - - - - - -
//  curl_exec_follow
// - - - - - - - - - - - - - - - - - - -	

function curl_exec_follow($ch, $maxredirect = 1) {

    if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    } else {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        $newurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $rch = curl_copy_handle($ch);

        curl_setopt($rch, CURLOPT_HEADER, true);
        curl_setopt($rch, CURLOPT_NOBODY, true);
        curl_setopt($rch, CURLOPT_RETURNTRANSFER, true);

        do {
            curl_setopt($rch, CURLOPT_URL, $newurl);
            $header = curl_exec($rch);
            if (curl_errno($rch)) {
                $code = 0;
            } else {
                $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
                if ($code == 301 || $code == 302) {
                    preg_match('/Location:(.*?)\n/', $header, $matches);
                    $newurl = trim(array_pop($matches));
                } else {
                    $code = 0;
                }
            }
        } while ($code && $maxredirect--);
        curl_close($rch);
        curl_setopt($ch, CURLOPT_URL, $newurl);
    }
    return curl_exec($ch);
}
