<?php

_resize($_GET["url"], $_GET["width"]);

function _resize($url, $maxwidth)
{
    $imageSize = getimagesize($url);
    $width = $imageSize[0];
    $height = $imageSize[1];
    if ($width > $maxwidth) {
        _resizeMain($url, $width, $height, $maxwidth);
    } else {
        readfile($_GET["url"]);
    }
}

function _resizeMain($url, $width, $height, $maxwidth)
{
    // 画像の拡張子ごとにsrcImageの取得を分ける
    $imageType = exif_imagetype($url);
    switch ($imageType) {
        case IMAGETYPE_GIF:
            $srcImage = imagecreatefromgif($url);
            break;
        case IMAGETYPE_JPEG:
            $srcImage = imagecreatefromjpeg($url);
            break;
        case IMAGETYPE_PNG:
            $srcImage = imagecreatefrompng($url);
            break;
    }
    // サムネイル用の高さの取得
    $thumbHeight = round($height * $maxwidth / $width);
    $thumbImage = imagecreatetruecolor($maxwidth, $thumbHeight);

    // PNGの透過をON
    if ($imageType == IMAGETYPE_PNG) {
        //ブレンドモードを無効にする
        imagealphablending($thumbImage, false);
        //完全なアルファチャネル情報を保存するフラグをonにする
        imagesavealpha($thumbImage, true);
    }

    // 再サンプリングをおこなう
    imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, $maxwidth,
    $thumbHeight, $width, $height);
    // 画像の拡張子ごとに保存
    switch($imageType) {
        case IMAGETYPE_GIF:
            header("Content-type: image/gif");
            imagegif($thumbImage);
            break;
        case IMAGETYPE_JPEG:
            header("Content-type: image/jpg");
            imagejpeg($thumbImage);
            break;
        case IMAGETYPE_PNG:
            header("Content-type: image/png");
            imagepng($thumbImage);
            break;
    }

}
