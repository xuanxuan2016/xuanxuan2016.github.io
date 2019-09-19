---
layout:     post
title:      "php添加水印"
subtitle:   "php watermark"
date:       2019-09-16 20:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - linux
    - python
---

## 概述

<p>
项目需求，用户可上传word或pdf文件，在前端页面选择合适位置添加水印图片，后端合同新的pdf文件供用户下载。
</p>

<p>
主体逻辑流程图：
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-09-16-php-watermark/tu_1.png?raw=true)

## word转pdf

<p>
通过<a href="https://github.com/PHPOffice/PHPWord" target="_blank">PHPWord库</a>打开word文件，然后转存为pdf文件。
</p>

```
require_once 'bootstrap.php';
use PhpOffice\PhpWord\Settings;

Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, BASE_PATH . '/vendor/dompdf/dompdf');

//word2007
$phpWord = \PhpOffice\PhpWord\IOFactory::load('helloWorld.docx');
//word97
$phpWord = \PhpOffice\PhpWord\IOFactory::load('helloWorld.doc','MsDoc');

//word转pdf 
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
$objWriter->save('helloWorld2.pdf');
```

## 前端显示pdf

<p>
将pdf文件在后台转换成<code>base64</code>编码，前台通过<a href="http://mozilla.github.io/pdf.js/examples/" target="_blank">pdfjs</a>显示。
</p>

```
#php
$strFilename = 'watermark_example.pdf';
$strImageData = fread(fopen($strFilename, 'r'), filesize($strFilename));
file_put_contents('base64_encode.txt', base64_encode($strImageData));
```

```
#html
<html>
	<head>		
		<script src="build/pdf.js"></script>
		
		<style type="text/css">
			body{
				display: flex;
				flex-direction: column;
				align-items: center;
			}
			.canvas {
			  border:1px solid black;
			  width:910px;
			  height:1288px;
			  margin-top: 5px;
			}
		</style>
	</head>
	<body style="text-align:center;">
	</body>
	<script>
			// atob() is used to convert base64 encoded PDF to binary-like data.
			// (See also https://developer.mozilla.org/en-US/docs/Web/API/WindowBase64/
			// Base64_encoding_and_decoding.)
			
			var pdfData=atob('后台base64编码');
			// Loaded via <script> tag, create shortcut to access PDF.js exports.
			//console.log(PDFJS);
			var pdfjsLib = window['pdfjs-dist/build/pdf'];
			console.log(pdfjsLib);
			// The workerSrc property shall be specified.
			pdfjsLib.GlobalWorkerOptions.workerSrc = 'build/pdf.worker.js';
			const CMAP_URL = 'https://cdn.jsdelivr.net/npm/pdfjs-dist@2.0.943/cmaps/';
			
			var totalNum=0,pdfObj=null;
			
			// Using DocumentInitParameters object to load binary data.
			var loadingTask = pdfjsLib.getDocument({data: pdfData,cMapUrl: CMAP_URL,cMapPacked: true});
			loadingTask.promise.then(function(pdf) {
			  console.log('PDF loaded');  
			  pdfObj=pdf;
			  totalNum=pdf._pdfInfo.numPages;
			  queueRenderPage(1);
			  return;
			  // Fetch the first page
			  var pageNumber = 2;
			  for(var i=1;i<=pdf._pdfInfo.numPages;i++){
					var canvas=document.createElement('canvas');
					canvas.setAttribute('id','canvas'+i);
					canvas.setAttribute('class','canvas');
					document.body.appendChild(canvas);
				    pdf.getPage(i).then(function(page) {
					console.log('Page loaded');
					var scale = 1.53;
					var viewport = page.getViewport({scale: scale});

					// Prepare canvas using PDF page dimensions
					//var canvas = document.getElementById('the-canvas');
					var context = canvas.getContext('2d');
					canvas.width = viewport.width;
					canvas.height = viewport.height;
					//canvas.height = viewport.width/592.28 * 841.229;
					// Render PDF page into canvas context
					var renderContext = {
					  canvasContext: context,
					  viewport: viewport
					};
					var renderTask = page.render(renderContext);
					renderTask.promise.then(function () {
					  console.log('Page rendered');
					});
				  });
			  }
			}, function (reason) {
			  // PDF loading error
			  console.error(reason);
			});
			
			function queueRenderPage(i) {
				var canvas=document.createElement('canvas');
				canvas.setAttribute('id','canvas'+i);
				canvas.setAttribute('class','canvas');
				document.body.appendChild(canvas);
				pdfObj.getPage(i).then(function(page) {
					console.log('Page loaded:'+i);
					var scale = 1.53;
					var viewport = page.getViewport({scale: scale});

					// Prepare canvas using PDF page dimensions
					//var canvas = document.getElementById('the-canvas');
					var context = canvas.getContext('2d');
					canvas.width = viewport.width;
					canvas.height = viewport.height;
					//canvas.height = viewport.width/592.28 * 841.229;
					// Render PDF page into canvas context
					var renderContext = {
					  canvasContext: context,
					  viewport: viewport
					};
					var renderTask = page.render(renderContext);
					renderTask.promise.then(function () {
						console.log('Page rendered:'+i);
						i++;
						if(i<=totalNum){
							queueRenderPage(i);
						}
					});
			  });
			}

		</script>
</html>
```

## python添加水印

<p>
不同语言都有添加水印的方法，这里采用python来实现。
</p>

```
import PyPDF2
from reportlab.pdfgen import canvas
from reportlab.lib.units import cm
from PyPDF2 import PdfFileWriter, PdfFileReader

#使用图片生成水印pdf，用于与需要的pdf也合并
def create_watermark(f_jpg):
    #默认大小为21cm*29.7cm
    f_pdf = 'mark.pdf'
    w_pdf = 21*cm
    h_pdf = 29.7*cm
                                                                                          
    c = canvas.Canvas(f_pdf, pagesize = (w_pdf, h_pdf))
    #设置坐标系为左上角
    c.translate(0*cm, 29.7*cm)
    #c.scale(1, -1)
    c.setFillAlpha(1.0) #设置透明度
    #这里的单位是物理尺寸
    c_width=200/911*21;
    #高度为负数-图片高度
    c_height=(600/911*21*-1)-5;
    print(c_width,c_height)
    c.drawImage(f_jpg, c_width*cm, c_height*cm, 5*cm, 5*cm)
    c.save()

def add_watermark_tmp(wmFile,pageObj):
    #打开水印pdf文件
    wmFileObj = open(wmFile,'rb')
    
    #创建pdfReader对象，把打开的水印pdf传入
    pdfReader = PyPDF2.PdfFileReader(wmFileObj)
    
    #将水印pdf的首页与传入的原始pdf的页进行合并
    pageObj.mergePage(pdfReader.getPage(0))
    #加了下面这行，就合成失败了
    #wmFileObj.close()
    return pageObj

def add_watermark(pdf_file_in, pdf_file_mark, pdf_file_out):
    pdf_output = PdfFileWriter()
    input_stream = open(pdf_file_in, 'rb')
    pdf_input = PdfFileReader(input_stream,True)
                                                                               
    # 获取PDF文件的页数
    #pageNum = pdf_input.getNumPages()
    #读入水印pdf文件
    pdf_watermark = PdfFileReader(open(pdf_file_mark, 'rb'))
    # 给每一页打水印
    for i in range(pdf_input.numPages):
        page = pdf_input.getPage(i)
        page.mergePage(pdf_watermark.getPage(0))
        page.compressContentStreams()   #压缩内容
        pdf_output.addPage(page)
    newFile = open(pdf_file_out,'wb')
    pdf_output.write(newFile)
    newFile.close()

def main():
    
    #水印pdf的名称
    watermark = 'mark.pdf'

    #原始pdf的名称
    origFileName = 'example1.pdf'

    #合并后新的pdf名称
    newFileName = 'watermark_example.pdf'

    #打开原始的pdf文件,获取文件指针
    pdfFileObj = open(origFileName,'rb')

    #创建reader对象
    pdfReader = PyPDF2.PdfFileReader(pdfFileObj)

    #创建一个指向新的pdf文件的指针
    pdfWriter = PyPDF2.PdfFileWriter()

    #通过迭代将水印添加到原始pdf的每一页
    for page in range(pdfReader.numPages):
        wmPageObj = add_watermark_tmp(watermark,pdfReader.getPage(page))
        
        #将合并后的即添加了水印的page对象添加到pdfWriter
        pdfWriter.addPage(wmPageObj)

    #打开新的pdf文件
    newFile = open(newFileName,'wb')
    #将已经添加完水印的pdfWriter对象写入文件
    pdfWriter.write(newFile)

    #关闭原始和新的pdf
    pdfFileObj.close()
    newFile.close()


if __name__ == '__main__':
    #方法1
    add_watermark('test.pdf','mark.pdf','watermark_example.pdf')
    #方法2
    #main()
    #生成水印
    #create_watermark('timg.jpg')
```

<p>
在处理某些pdf时出现了如下的decode错误，修改了底层忽视错误。
</p>

```
UnicodeDecodeError: 'utf-8' codec can't decode byte 0xcb in position 8: invalid continuation byte
```

```
#\Python36-32\Lib\site-packages\PyPDF2\generic.py
class NameObject(str, PdfObject):
    delimiterPattern = re.compile(b_(r"\s+|[\(\)<>\[\]{}/%]"))
    surfix = b_("/")

    def writeToStream(self, stream, encryption_key):
        stream.write(b_(self))

    def readFromStream(stream, pdf):
        debug = False
        if debug: print((stream.tell()))
        name = stream.read(1)
        if name != NameObject.surfix:
            raise utils.PdfReadError("name read error")
        name += utils.readUntilRegex(stream, NameObject.delimiterPattern, 
            ignore_eof=True)
        if debug: print(name)
        try:
            #添加了ignore
            return NameObject(name.decode('utf-8',"ignore"))
        except (UnicodeEncodeError, UnicodeDecodeError) as e:
            # Name objects should represent irregular characters
            # with a '#' followed by the symbol's hex number
            if not pdf.strict:
                warnings.warn("Illegal character in Name Object", utils.PdfReadWarning)
                return NameObject(name)
            else:
                raise utils.PdfReadError("Illegal character in Name Object")

    readFromStream = staticmethod(readFromStream)
```

## 参考资料

[pdfjs](http://mozilla.github.io/pdf.js/examples/)

[关于使用pdfjs预览PDF文件](https://www.jianshu.com/p/df5f9726cbbf)

[PHPWord中文手册整理](https://segmentfault.com/a/1190000019479817?utm_source=tag-newest)

[php如何给pdf加上文字水印和图片水印](https://blog.csdn.net/everdayPHP/article/details/73811937)
