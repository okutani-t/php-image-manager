<?php
/**
 * 汎用的なfunctionをまとめたファイル
 * 利用方法: このままutils.phpを読み込ませる
 * require_once("utils.php")
 *
 * @author okutani
 * @category Util Methods
 */

/**
 * htmlspecialcharsが長いので省略したもの
 *
 * @param string $str
 * @return string htmlspecialcharsを行った結果
 */
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * リダイレクト処理
 * 現在表示してるパスに飛ぶ
 */
function redirect()
{
    header("Location: " .
    (empty($_SERVER["HTTPS"]) ? "http://" : "https://") .
    $_SERVER["HTTP_HOST"] .
    $_SERVER["REQUEST_URI"]);
    exit;
}

/**
 * デバッグモードの切り替え
 * trueならデバッグモード
 */
function debugMode($flag=true)
{
    ini_set('display_errors', $flag ? 1 : 0);  // エラーを表示するか否か
    error_reporting(E_ALL ^ E_NOTICE);          // NOTICE エラー以外の全てのエラーを表示
}

/**
 * 配列の最初のkeyを取得
 *
 * @param array $arr
 * @return string 配列の最後のキー
 */
function firstKey($arr)
{
    reset($arr);
    return key($arr);
}
