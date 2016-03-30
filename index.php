<?php require_once "Controller.php"; ?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Image Uploader</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/css/materialize.min.css">
    <link rel="stylesheet" href="style.css">
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
    <![endif]-->
</head>
<body>
    <!-- header -->
    <header class="header">
        <nav>
            <div class="nav-wrapper">
                <div class="container">
                    <a href="" class="brand-logo">Image Uploader</a>
                    <ul class="right">
                        <li><a href="javascript:void(0);" class="window-close">
                            <i class="material-icons">close</i>
                        </a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <div class="container">

        <div class="row">
            <!-- upload button -->
            <div class="col m3 s12 upload-box">
                <form action="" method="post" enctype="multipart/form-data" id="my_form">
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo h(MAX_FILE_SIZE); ?>">
                    <input type="hidden" name="delPath" value="">
                    <input type="hidden" name="dlPath" value="">
                    <div class="file-field input-field">
                        <div class="w100p btn waves-effect waves-yellow btn-large">
                            <span>Upload! <i class="mdi-content-send right"></i></span>
                            <input type="file" name="image" id="my_file">
                        </div>
                    </div>
                </form>
            </div>

            <!-- select box -->
            <form id="ym_select" action="" method="get">
                <div class="input-field col m3 s12">
                    <?php if(IS_SELECT_DIRS) : ?>
                        <select name="select_dir">
                            <?php foreach ($img_dir_paths as $key => $value) : ?>
                                <option value="<?php echo $key ?>" <?php if (basename(CURRENT_IMAGES_DIR) === $key) {
                                    echo "selected";
                                } ?>><?php echo $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else : ?>
                        <select name="ym">
                            <option value="" disabled <?php if (CURRENT_YM === "") {
                                echo "selected";
                            } ?>>年月を選択 [<?php echo date("Y年m月", time()); ?>]</option>
                            <?php foreach ($ym_list as $key => $value) : ?>
                                <option value="<?php echo $key ?>" <?php if (CURRENT_YM === $key) {
                                    echo "selected";
                                } ?>><?php echo $value ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </form>

            <!-- msg -->
            <?php if (isset($success)) : ?>
                <div class="col offset-m1 m4 s12 msg success card-panel teal lighten-2">
                    <h4 class="white-text center-align">
                        <?php echo h($success); ?>
                    </h4>
                </div>
            <?php endif; ?>
            <?php if (isset($error)) : ?>
                <div class="col offset-m1 m4 s12 msg error card-panel red lighten-1">
                    <h4 class="white-text center-align">
                        <?php echo h($error); ?>
                    </h4>
                </div>
            <?php endif; ?>
        </div><!-- /row -->

        <!-- img list -->
        <div class="row grid">
            <?php foreach ($images as $image) : ?>
                <!-- 画像の表示 -->
                <div class="card-box grid-item col l3 m4 s6">
                    <div class="card hoverable">
                        <div class="card-image">
                            <!-- <img class="materialboxed lazy contain" src="<?php // echo h($image); ?>" width="400" alt="Title"> -->
                            <img class="materialboxed lazy contain" data-original="thumb.php?url=<?php echo h($image); ?>&width=<?php echo h(THUMB_MAX_WIDTH); ?>" width="400" alt="Title">
                            <span class="card-title">Title</span>
                        </div>
                        <div class="card-content">
                            <div class="row m0">
                                <!-- Download btn -->
                                <div class="col s12 mb10">
                                    <a href="<?php echo h($image); ?>" class="w100p btn download-btn waves-effect waves-light teil lighten-2"
                                        data-img-name="<?php echo h($image); ?>" download="<?php echo h(basename($image)); ?>">
                                        Download
                                    </a>
                                </div>
                                <!-- path paste btn -->
                                <div class="col s12 mb10">
                                    <a href="javascript:send_img_url('<?php echo h(basename($image)); ?>')" class="w100p waves-effect waves-light btn teil lighten-2">
                                        画像を挿入
                                    </a>
                                </div>
                                <!-- Delete btn -->
                                <div class="col s12">
                                    <div class="w100p btn delete-btn waves-effect waves-light pink lighten-1"
                                    data-img-name="<?php echo h($image); ?>">
                                    Delete
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach ?>
    </div><!-- /img list row -->

</div><!-- /container -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.97.5/js/materialize.min.js"></script>
<script src="https://npmcdn.com/masonry-layout@4.0/dist/masonry.pkgd.min.js"></script>
<script src="https://npmcdn.com/imagesloaded@4.1/imagesloaded.pkgd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazyload/1.9.1/jquery.lazyload.js"></script>
<script>
$(function(){
    // メッセージのフェードアウト
    $('.msg').fadeOut(4000);
    // materialize select
    $('select').material_select();
    $('[name=ym], [name=select_dir]').change(function() {
        $("#ym_select").submit();
    });
    // submit
    $("#my_file").on("change", function() {
        $("#my_form").submit();
    });
    // 画像削除
    $(".delete-btn").click(function() {
        if (confirm("本当に削除しますか？")) {
            $("[name=delPath]").val($(this).data("img-name"));
            $("#my_form").submit();
        }
    });
    // window close
    $(".window-close").click(function() {
        if (confirm("本当にウィンドウを閉じますか？")) {
            window.close();
            return false;
        }
    });
    // masonry
    var $grid = $('.grid');
    $grid.imagesLoaded(function(){
        $grid.masonry({
            itemSelector: '.grid-item',
            // isFitWidth: true,
            isAnimated: true
        });
    });
    // lazy load
    $("img.lazy").lazyload({
        threshold: 400,
        effect: "fadeIn",
        effect_speed: 3000,
    });
});
// send image url
function send_img_url(img_url) {
    if(!window.opener || window.opener.closed){
		window.alert('メインウィンドウが見つかりませんでした...');
	} else {
        window.opener.document.getElementById("img_url").value = img_url;
        window.close();
	}
}
</script>
</body>
</html>
