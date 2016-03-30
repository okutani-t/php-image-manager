<?php
/**
 * 画像をアップロード
 *
 * @access public
 * @author okutani
 * @package Class
 * @category Image Uploader
 */

namespace MyApp;

class ImageValidator
{
    /**
     * バリデーション実行
     *
     * @access public
     * @param string $imagePath $_FILES["image"]["tmp_name"]を渡す
     * @param integer $error $_FILES["image"]["error"]を渡す（要HTML: MAX_FILE_SIZE）
     * @return array [bool true|false, string png|$e->getMessage()] 画像かどうかのbool, エラーメッセージ
     */
    public static function check($imagePath, $error)
    {
        // GDライブラリの確認
        if (!function_exists("imagecreatetruecolor")) {
            return array(false, "GD not installed!");
        }

        try {
            // アップロード時のバリデーション（ファイルの有無・容量チェック）
            self::_validateUpload($imagePath, $error);

            // 画像の型のチェック（png/jpg/gif）
            $imageType = self::_validateImageType($imagePath);

            return array(true, $imageType);
        } catch (Exception $e) {
            return array(false, $e->getMessage());
        }

    }

    /**
     * アップロード時のバリデーション
     *
     * @access private
     * @return string true|エラー
     */
    private static function _validateUpload($imagePath, $error)
    {
        if (!isset($imagePath)) {
            throw new \Exception("Upload Error!");
        }

        switch($error) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE: // php.iniで設定された上限
            case UPLOAD_ERR_FORM_SIZE: // フォームで設定された上限
                throw new \Exception("ファイル容量が大きすぎます！");
            default:
                throw new \Exception("Err: " . $error);
        }

    }

    /**
     * 拡張子のバリデーション
     *
     * @access private
     * @return string 拡張子|エラー
     */
    private static function _validateImageType($imagePath)
    {
        $imageType = exif_imagetype($imagePath);
        switch ($imageType) {
            case IMAGETYPE_GIF:
                return "gif";
            case IMAGETYPE_JPEG:
                return "jpg";
            case IMAGETYPE_PNG:
                return "png";
            default:
                throw new \Exception("PNG/JPG/GIF以外はUPLOADできません！");
        }
    }

}
