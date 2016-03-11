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

class ImageUploader {

    private $_imageFileName;
    private $_imageType;

    /**
     * アップロードの実行
     *
     * @access public
     */
    public function upload()
    {
        try {
            // バリデーション
            $this->_validateUpload();
            // 拡張子のチェック
            $ext = $this->_validateImageType();
            // 保存
            $this->_save($ext);

            $_SESSION["success"] = "Upload Done!";
        } catch (\Exception $e) {
            $_SESSION["error"] = $e->getMessage();
        }

        // redirect
        if (SWITCH_SELECT_DIRS) {
            header("Location: " .
            (empty($_SERVER["HTTPS"]) ? "http://" : "https://") .
            $_SERVER["HTTP_HOST"] .
            $_SERVER["REQUEST_URI"]);
        } else {
            header("Location: " .
            (empty($_SERVER["HTTPS"]) ? "http://" : "https://") .
            $_SERVER["HTTP_HOST"] .
            parse_url($_SERVER["REQUEST_URI"])["path"]);
        }
        exit;
    }

    /**
     * 保存
     *
     * @access private
     * @param string $ext 拡張子
     */
    private function _save($ext)
    {
        $this->_imageFileName = sprintf(
            "%s_%s.%s",
            time(), // 現在時刻
            sha1(uniqid(mt_rand(), true)), // quniqidで一意なキー、sha1でハッシュ変換
            $ext // 拡張子
        );

        if (SWITCH_SELECT_DIRS) {
            // 現在選択しているディレクトリ
            $savePath = CURRENT_IMAGES_DIR . "/" . $this->_imageFileName;
        } else {
            // 現在の年月のディレクトリを作成
            $this->createDirs(IMAGES_DIR_YM_NOW);

            $savePath = IMAGES_DIR_YM_NOW . "/" . $this->_imageFileName;
        }
        // tmpファイルをリサイズ
        $this->_resize($_FILES["image"]["tmp_name"]);

        // tmpディレクトリから本番ディレクトリに移動
        $res = move_uploaded_file($_FILES["image"]["tmp_name"], $savePath);

        if ($res === false) {
            throw new \Exception("Could not upload!");
        }

    }

    /**
     * tmpファイルをリサイズ
     * まずはじめにリサイズするかどうか判断している
     *
     * @access private
     * @param string $ext
     */
    private function _resize($tmpPath)
    {
        $imageSize = getimagesize($tmpPath);
        $width = $imageSize[0];
        $height = $imageSize[1];
        if ($width > RESIZE_MAX_WIDTH) {
            $this->_resizeMain($tmpPath, $width, $height);
        }
    }

    /**
     * 拡張子を判別して実際にリサイズ
     *
     * @access private
     * @param string $tmpPath
     * @param int $width
     * @param int $height
     */
    private function _resizeMain($tmpPath, $width, $height)
    {
        // 画像の拡張子ごとにsrcImageの取得を分ける
        switch ($this->_imageType) {
            case IMAGETYPE_GIF:
                $srcImage = imagecreatefromgif($tmpPath);
                break;
            case IMAGETYPE_JPEG:
                $srcImage = imagecreatefromjpeg($tmpPath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = imagecreatefrompng($tmpPath);
                break;
        }
        // サムネイル用の高さの取得
        $thumbHeight = round($height * RESIZE_MAX_WIDTH / $width);
        $thumbImage = imagecreatetruecolor(RESIZE_MAX_WIDTH, $thumbHeight);

        // PNGの透過をON
        if ($this->_imageType == IMAGETYPE_PNG) {
            //ブレンドモードを無効にする
            imagealphablending($thumbImage, false);
            //完全なアルファチャネル情報を保存するフラグをonにする
            imagesavealpha($thumbImage, true);
        }

        // 再サンプリングをおこなう
        imagecopyresampled($thumbImage, $srcImage, 0, 0, 0, 0, RESIZE_MAX_WIDTH,
        $thumbHeight, $width, $height);
        // 画像の拡張子ごとに保存
        switch($this->_imageType) {
            case IMAGETYPE_GIF:
                imagegif($thumbImage, $tmpPath);
                break;
            case IMAGETYPE_JPEG:
                imagejpeg($thumbImage, $tmpPath);
                break;
            case IMAGETYPE_PNG:
                imagepng($thumbImage, $tmpPath);
                break;
        }

    }

    /**
     * 画像のゲッター
     *
     * @access public
     * @return array $images
     */
    public function getImages()
    {
        $images = array();
        $files = array();
        $imageDir = opendir(CURRENT_IMAGES_DIR);
        while (false !== ($file = readdir($imageDir))) {
            if ($file === "."  ||
                $file === ".." ||
                $file === ".gitkeep") {
                continue;
            }
            $files[] = $file;
            $images[] = "images/" . basename(CURRENT_IMAGES_DIR) . "/" . $file;
        }
        array_multisort($files, SORT_DESC, $images);
        return $images;
    }

    /**
     * 拡張子のバリデーション
     *
     * @access private
     * @return string 拡張子|エラー
     */
    private function _validateImageType()
    {
        $this->_imageType = exif_imagetype($_FILES["image"]["tmp_name"]);
        switch ($this->_imageType) {
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

    /**
     * アップロード時のバリデーション
     *
     * @access private
     * @return string true|エラー
     */
    private function _validateUpload()
    {
        if (!isset($_FILES["image"]) || !isset($_FILES["image"]["error"])) {
            throw new \Exception("Upload Error!");
        }

        switch($_FILES["image"]["error"]) {
            case UPLOAD_ERR_OK:
                return true;
            case UPLOAD_ERR_INI_SIZE: // php.iniで設定された上限
            case UPLOAD_ERR_FORM_SIZE: // フォームで設定された上限
                throw new \Exception("ファイル容量が大きすぎます！");
            default:
                throw new \Exception("Err: " . $_FILES["image"]["error"]);
        }

    }

    /**
     * 画像取得の成功・失敗のゲッター
     *
     * @access public
     * @return array [$success, $error]
     */
    public function getResults()
    {
        $success = null;
        $error = null;
        if (isset($_SESSION["success"])) {
            $success = $_SESSION["success"];
            unset($_SESSION["success"]);
        }
        if (isset($_SESSION["error"])) {
            $error = $_SESSION["error"];
            unset($_SESSION["error"]);
        }
        return [$success, $error];
    }

    /**
     * ディレクトリの生成
     * 指定したパスの途中にあるディレクトリも同時に生成される
     *
     * @param string args ディレクトリのパス
     */
    private function createDirs(/*args*/)
    {
        foreach (func_get_args() as $path) {
            // ディレクトリが存在していなければ生成
            if (!file_exists($path)) {
                // パーミッションの制限を解除
                umask(0);
                // ディレクトリ生成
                mkdir($path, '0777', true);
            }
        }
    }

}
