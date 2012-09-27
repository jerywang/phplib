<link href="uploadify/style.css" rel="stylesheet" type="text/css" media="screen" />
<script type="text/javascript" src="uploadify/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="uploadify/swfobject.js"></script>
<script type="text/javascript" src="uploadify/jquery.uploadify.v2.1.4.js"></script>
<body >

<script type="text/javascript">

$(function() {
    $('#file_upload').uploadify({
        'uploader' : 'uploadify/uploadify.swf',
        'script' : 'uploadify/uploadify.php',
        'cancelImg' : 'uploadify/cancel.png',
        'scriptAccess': 'always',
        'fileDataName': 'file',
        'fileDesc':'所有图片',
        'sizeLimit':1024*1024*10, // fileuplod limitsize only 2MB
        'fileExt':'*.jpg;*.png',
        'multi': true,
        'auto': true,
        'onError':function(){},
        'onComplete':function(event,queueID,fileObj,response,data){
            for(i=0;i<1;i++){
            	var url = response;
            	img=$('<img class="img" src="'+url+'"/>');
                $('#prev_image').append(img);
            }
            //var url = response;
            //img=$('<img class="img" src="'+url+'"/>');
        }
    });

});
</script>

<div id="basic-demo" class="demo">
    <div class="demo-box">
        <input id="file_upload" type="file" name="file" />
        <p><a href="javascript:$('#file_upload').uploadifyUpload()">Upload Files</a></p>
        <div id="prev_image"></div>
    </div>
</div>