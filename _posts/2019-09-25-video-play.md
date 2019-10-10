---
layout:     post
title:      "流媒体播放"
subtitle:   "video play"
date:       2019-09-25 20:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - linux
    - js
---

## 概要

<p>
项目中需要播放视频，由于视频可能比较大，需要做成流媒体播放。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-09-25-video-play/tu_1.png?raw=true)

## 前端

#### video.js

<p>
Video.js 是一个通用的在网页上嵌入视频播放器的JS库，支持指定视频完整路径或通过m3u8文件来播放视频。
</p>

##### 指定视频路径

<p>
直接配置html播放
</p>

```
<div>
	<video
    	id="my-playerff"
    	class="video-js vjs-big-play-centered"
    	controls
    	preload="auto"
    	data-setup="{}">
      <source src="14951040752.mp4"  type="video/mp4">
    </video>
</div>
```

<p>
通过js控制播放
</p>

```
<div>
	<video
		id="my-playerff"
		class="video-js vjs-big-play-centered"
		controls
		preload="auto">
	</video>
</div>
```

```
videojs('my-playerff',{},function(){
	var player=this;
	player.on('loadedmetadata',function(){
		console.log('loadedmetadata');
		//加载到元数据后开始播放视频
		player.play();
	});
	player.src('14951040752.mp4');
	player.load();
});
```

##### m3u8

<p>
m3u8文件内容，ts路径可自定义只要http请求可以访问。
</p>

```
#EXTM3U
#EXT-X-VERSION:3
#EXT-X-MEDIA-SEQUENCE:0
#EXT-X-ALLOW-CACHE:YES
#EXT-X-TARGETDURATION:16
#EXTINF:10.510522,
ts\output000.ts
#EXTINF:13.596933,
ts\output001.ts
#EXTINF:6.840178,
ts\output002.ts
#EXTINF:13.722056,
ts\output003.ts
#EXTINF:14.556222,
ts\output004.ts
#EXTINF:1.710044,
ts\output005.ts
#EXTINF:12.137133,
ts\output006.ts
#EXTINF:7.716056,
ts\output007.ts
#EXTINF:9.300967,
ts\output008.ts
#EXTINF:15.932600,
ts\output009.ts
#EXTINF:3.086422,
ts\output010.ts
#EXT-X-ENDLIST
```

<p>
直接配置html播放
</p>

```
<div>
	<video
    	id="my-playerff"
    	class="video-js vjs-big-play-centered"
    	controls
    	preload="auto">
      <source src="playlist2.m3u8"  type="application/x-mpegURL">
    </video>
</div>
```

<p>
通过js控制播放
</p>

```
<div>
	<video
		id="my-playerff"
		class="video-js vjs-big-play-centered"
		controls
		preload="auto">
	</video>
</div>
```

```
videojs('my-playerff',{},function(){
	var player=this;
	player.src({'src':'playlist2.m3u8','type':'application/x-mpegURL'});
	player.load();
});
```

##### 常用播放器样式设置

```
/*1.播放按钮变圆形*/
.video-js .vjs-big-play-button{
	font-size: 2.5em;
	line-height: 2.3em;
	height: 2.5em;
	width: 2.5em;
	-webkit-border-radius: 2.5em;
	-moz-border-radius: 2.5em;
	border-radius: 2.5em;
	background-color: #73859f;
	background-color: rgba(115,133,159,.5);
	border-width: 0.15em;
	margin-top: -1.25em;
	margin-left: -1.75em;
}
/* 中间的播放箭头 */
.vjs-big-play-button .vjs-icon-placeholder {
	font-size: 1.63em;
}
/* 加载圆圈 */
.vjs-loading-spinner {
	font-size: 2.5em;
	width: 2em;
	height: 2em;
	border-radius: 1em;
	margin-top: -1em;
	margin-left: -1.5em;
}

/*2.暂停时显示播放按钮*/
.vjs-paused .vjs-big-play-button,
.vjs-paused.vjs-has-started .vjs-big-play-button {
	display: block;
}
.video-js {
	cursor:pointer;
}

/*3.进度显示当前播放时间*/
.video-js .vjs-time-control{
	display:block;
}
.video-js .vjs-remaining-time{
	display: none;
}
```

##### 动态加载不同视频

<p>
通过后台获取<code>m3u8</code>文件，可校验用户权限，返回<code>ts</code>文件路径，路径中可增加<code>sign与有效时间</code>，便于后面对ts的访问控制。
</p>

```
play: function() {
    videojs('my-playerff', {}, function() {
        var player = this;
        player.src({'src': 'http://域名/web/common/common/gettslist', 'type': 'application/x-mpegURL'});
        player.load();
    });
}
```

## 后端

#### php

<p>
m3u8文件加载
</p>

```
#EXTM3U
#EXT-X-VERSION:3
#EXT-X-MEDIA-SEQUENCE:0
#EXT-X-ALLOW-CACHE:YES
#EXT-X-TARGETDURATION:16
#EXTINF:10.510522,
http://域名/web/common/common/gettsinfo?id=0
#EXTINF:13.596933,
http://域名/web/common/common/gettsinfo?id=1
#EXTINF:6.840178,
http://域名/web/common/common/gettsinfo?id=2
#EXTINF:13.722056,
http://域名/web/common/common/gettsinfo?id=3
#EXTINF:14.556222,
http://域名/web/common/common/gettsinfo?id=4
#EXTINF:1.710044,
http://域名/web/common/common/gettsinfo?id=5
#EXTINF:12.137133,
http://域名/web/common/common/gettsinfo?id=6
#EXTINF:7.716056,
http://域名/web/common/common/gettsinfo?id=7
#EXTINF:9.300967,
http://域名/web/common/common/gettsinfo?id=8
#EXTINF:15.932600,
http://域名/web/common/common/gettsinfo?id=9
#EXTINF:3.086422,
http://域名/web/common/common/gettsinfo?id=10
#EXT-X-ENDLIST
```

<p>
ts文件加载
</p>

```
$intSize = filesize($strFileName);
ob_end_clean();
$objFile = fopen($strFileName, "rb");

Header("Content-Transfer-Encoding: binary");
Header("Accept-Ranges: bytes");
Header("Content-Length:" . filesize($strFileName));
Header("Content-Type:video/mp2t");
#控制文件过期时间
Header("Cache-control:max-age=600");
Header("Expires:" . gmdate("D, d M Y H:i:s", strtotime('+10 minutes')) . 'GMT');

#向前端输出文件
while (!feof($objFile)) {
    echo fread($objFile, 32768);
}
fclose($objFile);
```

#### ffmpeg

<p>
FFmpeg是一套可以用来记录、转换数字音频、视频，并能将其转化为流的开源计算机程序。采用LGPL或GPL许可证。它提供了录制、转换以及流化音视频的完整解决方案。它包含了非常先进的音频/视频编解码库libavcodec，为了保证高可移植性和编解码质量，libavcodec里很多code都是从头开发的。
</p>

##### 安装

[下载地址](https://johnvansickle.com/ffmpeg/)

[官方文档](http://ffmpeg.org/ffmpeg.html)

```
[root@localhost bmsource]# wget https://johnvansickle.com/ffmpeg/builds/ffmpeg-git-i686-static.tar.xz
[root@localhost bmsource]# tar -xvf ffmpeg-git-i686-static.tar.xz
[root@localhost bmsource]# mv ffmpeg-git-20190605-i686-static/  /usr/local/
[root@localhost bmsource]# chmod -R 777 /usr/local/ffmpeg-git-20190605-i686-static/
[root@localhost bmsource]# ln /usr/local/bin/ffmpeg /usr/local/ffmpeg-git-20190605-i686-static/ffmpeg
[root@localhost bmsource]# ffmpeg -h //检测是否可以执行
```

##### 常用命令

```
#获取视频格式
/usr/local/ffmpeg-git-20190605-i686-static/ffprobe -show_format 14951040752.mp4
```

```
#视频格式转换
ffmpeg -i input.avi output.mp4
```

```
#视频压缩
#-s:设置压缩后的大小
ffmpeg -i 14951040752.mp4  -s 540*360 2.mp4
```

```
#获取视频第一帧
ffmpeg -i 14951040752.mp4 -frames:v 1  -f image2 a.png
```

```
#视频切片
ffmpeg -i output.ts -c copy -map 0 -f segment -segment_list playlist.m3u8 -segment_time 10 output%03d.ts
```

## 其它思考

#### cdn加速

- 可将ts文件放在cdn代理服务器，设置一定的过期时间，如果过期则从源服务器更新

#### 播放权限

- m3u8:后台获取此文件时，可以进行用户的验证
- ts:url增加sign（md5(key+视频原始url+时间戳)），后台判断sign是否有效

## 参考资料

[视频播放](http://naotu.baidu.com/file/a15e7c26283da50e0ecf85194da2312c?token=8416e0c84ac7f95f)

[videojs](https://docs.videojs.com/player)