<?php

require_once "utils.php";
require_once "config.php";
require_once "ImageValidator.class.php";
require_once "ImageUploader.class.php";

$uploader = new \MyApp\ImageUploader();

// delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["delPath"])) {
    try {
        $isDelImg = unlink(CURRENT_IMAGES_DIR . "/" . basename($_POST["delPath"]));
        if ($isDelImg) {
            $_SESSION["success"] = "Delete Done!";
        } else {
            throw new \Exception("Image can't be deleted!");
        }
    } catch (\Exception $e) {
        $_SESSION["error"] = $e->getMessage();
    }
    redirect();
}

// upload
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uploader->upload();
}

// サクセス・エラー文の取得
list($success, $error) = $uploader->getResults();

// 画像ファイルパスの取得
$images = $uploader->getImages();

// 画像ディレクトリの捜査
$images_dirs = opendir(__DIR__ . "/images/");
$ym_list = array();
$select_dir_list = array();
$files = array();
while (false !== ($file = readdir($images_dirs))) {
    if ($file === "."  ||
        $file === ".." ||
        $file === ".gitkeep") {
        continue;
    }
    // ディレクトリが空なら削除
    if (count(glob(__DIR__ . "/images/" . $file . "/*")) == 0) {
        rmdir(__DIR__ . "/images/" . $file);
    }
    $files[] = $file;
    // ドロップダウン用の配列を作成
    if (IS_SELECT_DIRS) {
        $select_dir_list[] = $file;
    } else {
        $ym_list[$file] = date("Y年m月", strtotime($file));
        array_multisort($files, SORT_DESC, $ym_list);
    }
    // sort
}
