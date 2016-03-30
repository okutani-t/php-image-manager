<?php

session_start();

/////////////// ここから ///////////////

/**
 * 開発true, 本番false
 */
debugMode(true);

/**
 * 格納するディレクトリを直接指定したい場合true
 */
define("IS_SELECT_DIRS", false);

/**
 * アップロードできるファイル容量の上限
 * 3 * 1024 * 1024 = 3MB
 */
define("MAX_FILE_SIZE", 3 * 1024 * 1024);

/**
 * 自動リサイズする横幅の値
 * これ以上大きかったら設定した値にリサイズされる
 */
define("RESIZE_MAX_WIDTH", 2000);

/**
 * サムネイル画像の横幅
 */
define("THUMB_MAX_WIDTH", 400);

/**
 * 現在の年月を含んだ画像パス
 */
define("IMAGES_DIR_YM_NOW", __DIR__ . "/images/" . date("Y-m", time()));

/**
 * 直接ディレクトリを指定したい場合
 * あらかじめディレクトリを作成しておく
 * "images以下にあるディレクトリ名" => "ディレクトリにつけたい別名", で記述
 */
if (IS_SELECT_DIRS) {
    $img_dir_paths = array(
        "mydir" => "私専用の画像",
        "other" => "他の人の画像"
    );
}

/////////////// ここまで ///////////////

/**
 * 画像までのパス
 *
 * GETの値が無い・一致しない＆ディレクトリが存在しない場合、最新年月の画像パスを表示
 * 画像がimagesに一枚もなければimagesまでのディレクトリを定義
 */
if (IS_SELECT_DIRS) {
    if (isset($_GET["select_dir"]) &&
        array_key_exists($_GET["select_dir"], $img_dir_paths)) { // 直接ディレクトリ指定の場合
        $current_images_dir = __DIR__ . "/images/" . $_GET["select_dir"];
    } else {
        $current_images_dir = __DIR__ . "/images/" . firstKey($img_dir_paths);
    }
} else {
    $current_ym = "";
    // 現在指定している年月とそのディレクトリを定義
    if (isset($_GET["ym"]) &&
    preg_match("/\A[0-9]{4}-[0-9]{2}\z/", $_GET["ym"]) &&
    file_exists(__DIR__ . "/images/" . $_GET["ym"])) {
        $current_ym = $_GET["ym"];
        $current_images_dir = __DIR__ . "/images/" . $_GET["ym"];
    } elseif (file_exists(IMAGES_DIR_YM_NOW)) { // 上記に一致しなければ最新のディレクトリを開く
        $current_images_dir = IMAGES_DIR_YM_NOW;
    } else { // 画像が一枚もない場合
        $current_images_dir = __DIR__ . "/images";
    }
    // 現在表示している月と現在のディレクトリ場所の定義
    define("CURRENT_YM", $current_ym);
}

define("CURRENT_IMAGES_DIR", $current_images_dir);
