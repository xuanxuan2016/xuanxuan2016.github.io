---
layout:     post
title:      "php���ˮӡ"
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

## ����

<p>
��Ŀ�����û����ϴ�word��pdf�ļ�����ǰ��ҳ��ѡ�����λ�����ˮӡͼƬ����˺�ͬ�µ�pdf�ļ����û����ء�
</p>

<p>
�����߼�����ͼ��
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-09-16-php-watermark/tu_1.png?raw=true)

## wordתpdf

<p>
ͨ��<a href="https://github.com/PHPOffice/PHPWord" target="_blank">PHPWord��</a>��word�ļ���Ȼ��ת��Ϊpdf�ļ���
</p>

```
require_once 'bootstrap.php';
use PhpOffice\PhpWord\Settings;

Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, BASE_PATH . '/vendor/dompdf/dompdf');

//word2007
$phpWord = \PhpOffice\PhpWord\IOFactory::load('helloWorld.docx');
//word97
$phpWord = \PhpOffice\PhpWord\IOFactory::load('helloWorld.doc','MsDoc');

//wordתpdf 
$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'PDF');
$objWriter->save('helloWorld2.pdf');
```

## ǰ����ʾpdf

<p>
��pdf�ļ��ں�̨ת����<code>base64</code>���룬ǰ̨ͨ��<a href="http://mozilla.github.io/pdf.js/examples/" target="_blank">pdfjs</a>��ʾ��
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
			
			var pdfData=atob('��̨base64����');
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

## python���ˮӡ

<p>
��ͬ���Զ������ˮӡ�ķ������������python��ʵ�֡�
</p>

```
import PyPDF2
from reportlab.pdfgen import canvas
from reportlab.lib.units import cm
from PyPDF2 import PdfFileWriter, PdfFileReader

#ʹ��ͼƬ����ˮӡpdf����������Ҫ��pdfҲ�ϲ�
def create_watermark(f_jpg):
    #Ĭ�ϴ�СΪ21cm*29.7cm
    f_pdf = 'mark.pdf'
    w_pdf = 21*cm
    h_pdf = 29.7*cm
                                                                                          
    c = canvas.Canvas(f_pdf, pagesize = (w_pdf, h_pdf))
    #��������ϵΪ���Ͻ�
    c.translate(0*cm, 29.7*cm)
    #c.scale(1, -1)
    c.setFillAlpha(1.0) #����͸����
    #����ĵ�λ������ߴ�
    c_width=200/911*21;
    #�߶�Ϊ����-ͼƬ�߶�
    c_height=(600/911*21*-1)-5;
    print(c_width,c_height)
    c.drawImage(f_jpg, c_width*cm, c_height*cm, 5*cm, 5*cm)
    c.save()

def add_watermark_tmp(wmFile,pageObj):
    #��ˮӡpdf�ļ�
    wmFileObj = open(wmFile,'rb')
    
    #����pdfReader���󣬰Ѵ򿪵�ˮӡpdf����
    pdfReader = PyPDF2.PdfFileReader(wmFileObj)
    
    #��ˮӡpdf����ҳ�봫���ԭʼpdf��ҳ���кϲ�
    pageObj.mergePage(pdfReader.getPage(0))
    #�����������У��ͺϳ�ʧ����
    #wmFileObj.close()
    return pageObj

def add_watermark(pdf_file_in, pdf_file_mark, pdf_file_out):
    pdf_output = PdfFileWriter()
    input_stream = open(pdf_file_in, 'rb')
    pdf_input = PdfFileReader(input_stream,True)
                                                                               
    # ��ȡPDF�ļ���ҳ��
    #pageNum = pdf_input.getNumPages()
    #����ˮӡpdf�ļ�
    pdf_watermark = PdfFileReader(open(pdf_file_mark, 'rb'))
    # ��ÿһҳ��ˮӡ
    for i in range(pdf_input.numPages):
        page = pdf_input.getPage(i)
        page.mergePage(pdf_watermark.getPage(0))
        page.compressContentStreams()   #ѹ������
        pdf_output.addPage(page)
    newFile = open(pdf_file_out,'wb')
    pdf_output.write(newFile)
    newFile.close()

def main():
    
    #ˮӡpdf������
    watermark = 'mark.pdf'

    #ԭʼpdf������
    origFileName = 'example1.pdf'

    #�ϲ����µ�pdf����
    newFileName = 'watermark_example.pdf'

    #��ԭʼ��pdf�ļ�,��ȡ�ļ�ָ��
    pdfFileObj = open(origFileName,'rb')

    #����reader����
    pdfReader = PyPDF2.PdfFileReader(pdfFileObj)

    #����һ��ָ���µ�pdf�ļ���ָ��
    pdfWriter = PyPDF2.PdfFileWriter()

    #ͨ��������ˮӡ��ӵ�ԭʼpdf��ÿһҳ
    for page in range(pdfReader.numPages):
        wmPageObj = add_watermark_tmp(watermark,pdfReader.getPage(page))
        
        #���ϲ���ļ������ˮӡ��page������ӵ�pdfWriter
        pdfWriter.addPage(wmPageObj)

    #���µ�pdf�ļ�
    newFile = open(newFileName,'wb')
    #���Ѿ������ˮӡ��pdfWriter����д���ļ�
    pdfWriter.write(newFile)

    #�ر�ԭʼ���µ�pdf
    pdfFileObj.close()
    newFile.close()


if __name__ == '__main__':
    #����1
    add_watermark('test.pdf','mark.pdf','watermark_example.pdf')
    #����2
    #main()
    #����ˮӡ
    #create_watermark('timg.jpg')
```

<p>
�ڴ���ĳЩpdfʱ���������µ�decode�����޸��˵ײ���Ӵ���
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
            #�����ignore
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

## �ο�����

[pdfjs](http://mozilla.github.io/pdf.js/examples/)

[����ʹ��pdfjsԤ��PDF�ļ�](https://www.jianshu.com/p/df5f9726cbbf)

[PHPWord�����ֲ�����](https://segmentfault.com/a/1190000019479817?utm_source=tag-newest)

[php��θ�pdf��������ˮӡ��ͼƬˮӡ](https://blog.csdn.net/everdayPHP/article/details/73811937)
