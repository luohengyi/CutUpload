<?php


class Upload
{
    private $filepath = './upload/cut'; //上传目录
    private $tmpPath; //PHP文件临时目录
    private $blobNum; //第几个文件块
    private $totalBlobNum; //文件块总数
    private $fileName; //文件名

    /**
     * Upload constructor.
     * @param $tmpPath  上传目录
     * @param $blobNum  第几个文件块
     * @param $totalBlobNum 文件块总数
     * @param $fileName fileName
     */
    public function __construct($tmpPath, $blobNum, $totalBlobNum, $fileName)
    {
        $this->tmpPath = $tmpPath;
        $this->blobNum = $blobNum;
        $this->totalBlobNum = $totalBlobNum;
        $this->fileName = $fileName;
        //文件上传功能
        $this->moveFile();
        //文件组装
        $this->fileMerge();
    }

    //判断是否是最后一块，如果是则进行文件合成并且删除文件块
    function fileMerge()
    {
        $count = glob($this->filepath . "/" . md5($this->fileName));

        // 判断文件上传是否完成
        if ($count == $this->blobNum) {
            //todo 处理文件未上穿完成
            return false;
        }

        if ($this->blobNum == $this->totalBlobNum) {
            $blob = null;
            for ($i = 1; $i <= $this->totalBlobNum; $i++) {
                //todo 此处将文件依次读入内存中如何文件过大，可能将内存撑爆，应将文件依次写入合并文件中。
                $blob = file_get_contents($this->filepath . '/' . md5($this->fileName) . '/' . $this->fileName . '__' . $i);
                file_put_contents($this->filepath . '/' . $this->fileName, $blob, FILE_APPEND);
            }
            $this->deleteFileBlob();
        }
    }

    //删除文件块
    function deleteFileBlob()
    {
        for ($i = 1; $i <= $this->totalBlobNum; $i++) {
            @unlink($this->filepath . '/' . md5($this->fileName) . '/' . $this->fileName . '__' . $i);
        }
        rmdir($this->filepath . '/' . md5($this->fileName));
    }

    //移动文件
    function moveFile()
    {
        $this->touchDir($this->fileName);
        $filename = $this->filepath . '/' . md5($this->fileName) . '/' . $this->fileName . '__' . $this->blobNum;
        move_uploaded_file($this->tmpPath, $filename);
    }

    //API返回数据
    function apiReturn()
    {
        $data = null;
        if ($this->blobNum == $this->totalBlobNum) {
            if (file_exists($this->filepath . '/' . $this->fileName)) {
                $data['code'] = 2;
                $data['msg'] = 'success';
                $data['file_path'] = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['DOCUMENT_URI']) . str_replace('.', '', $this->filepath) . '/' . $this->fileName;
            }
        } else {
            if (file_exists($this->filepath . '/' . $this->fileName . '__' . $this->blobNum)) {
                $data['code'] = 1;
                $data['msg'] = 'waiting for all';
                $data['file_path'] = '';
            }
        }
        header('Content-type: application/json');
        echo json_encode($data);
    }

    //建立上传文件夹
    function touchDir($fileName)
    {
        $pathname = $this->filepath . "/" . md5($fileName);
        if (!file_exists($pathname)) {

            return mkdir($pathname, 0777, $recursive = true);
        }
    }
}

//实例化并获取系统变量传参
$upload = new Upload($_FILES['file']['tmp_name'], $_POST['blob_num'], $_POST['total_blob_num'], $_POST['file_name']);
////调用方法，返回结果
$upload->apiReturn();

