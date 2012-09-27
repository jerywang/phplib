<?php
define ( "big", "big/" );
define ( "thumb", "thumb/" );
$type_arr = array ("image/jpeg", "image/pjpg", "image/gif", "image/png" );

if($_FILES){
if (is_uploaded_file ( $_FILES ['file_image'] ['tmp_name'] )) {
    $image_type = $_FILES ['file_image'] ['type'];
    if (! in_array ( $image_type, $type_arr )) {
        echo "不支持这种格式";
    }
    else {
        $name = $_POST ['name'];
        $result2 = move_uploaded_file ( $_FILES ['file_image'] ['tmp_name'], big . date ( "his", time () ) . "." . fileext ( $_FILES ['file_image'] ['name'] ) );
        //生成缩略图
        cutphoto ( big . date ( "his", time () ) . ".jpg", thumb . date ( "his", time () ) . "_thumb.jpg", 256, 192 );
        echo '<img src="'.thumb . date ( "his", time () ) . '_thumb.jpg" />';
        if ($result2 == 1){
            echo "sucess!";
        }
        else{
            echo "fail!";
        }
    }
    //echo $image_type;
}
}
//获取文件后缀名函数
function fileext($filename) {
    return substr ( strrchr ( $filename, '.' ), 1 );
}

//生成缩略图函数
function cutphoto($o_photo, $d_photo, $width, $height) {
    $temp_img = imagecreatefromjpeg ( $o_photo );
    $o_width = imagesx ( $temp_img ); //取得原图宽
    $o_height = imagesy ( $temp_img ); //取得原图高
    //判断处理方法
    if ($width > $o_width || $height > $o_height) { //原图宽或高比规定的尺寸小,进行压缩


        $newwidth = $o_width;
        $newheight = $o_height;
        if ($o_width > $width) {
            $newwidth = $width;
            $newheight = $o_height * $width / $o_width;
        }
        if ($newheight > $height) {
            $newwidth = $newwidth * $height / $newheight;
            $newheight = $height;
        }
        //缩略图片
        $new_img = imagecreatetruecolor ( $newwidth, $newheight );
        imagecopyresampled ( $new_img, $temp_img, 0, 0, 0, 0, $newwidth, $newheight, $o_width, $o_height );
        imagejpeg ( $new_img, $d_photo );
        imagedestroy ( $new_img );

    }
    else { //原图宽与高都比规定尺寸大,进行压缩后裁剪
        if ($o_height * $width / $o_width > $height) { //先确定width与规定相同,如果height比规定大,则ok
            $newwidth = $width;
            $newheight = $o_height * $width / $o_width;
            $x = 0;
            $y = ($newheight - $height) / 2;
        } else { //否则确定height与规定相同,width自适应
            $newwidth = $o_width * $height / $o_height;
            $newheight = $height;
            $x = ($newwidth - $width) / 2;
            $y = 0;
        }
        //缩略图片
        $new_img = imagecreatetruecolor ( $newwidth, $newheight );
        imagecopyresampled ( $new_img, $temp_img, 0, 0, 0, 0, $newwidth, $newheight, $o_width, $o_height );
        imagejpeg ( $new_img, $d_photo );
        imagedestroy ( $new_img );
        $temp_img = imagecreatefromjpeg ( $d_photo );
        $o_width = imagesx ( $temp_img ); //取得缩略图宽
        $o_height = imagesy ( $temp_img ); //取得缩略图高
        //裁剪图片
        $new_imgx = imagecreatetruecolor ( $width, $height );
        imagecopyresampled ( $new_imgx, $temp_img, 0, 0, $x, $y, $width, $height, $width, $height );
        imagejpeg ( $new_imgx, $d_photo );
        imagedestroy ( $new_imgx );
    }

}
?>
<form method="post" enctype="multipart/form-data">上传人的名字：<input
    type="text" name="name" value="" /> <br />
    上传图片：<input type="file" name="file_image" value="" /> <input
    type="submit" value="submit" name="submit" /></form>
