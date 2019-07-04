<?php


interface CutUpload
{

    /**
     * 合并文件
     * @return mixed
     */
    function fileMerge();


    /**
     * 删除临时文件
     * @return mixed
     */
    function deleteFileBlob();


    /**
     * 上传临时文件
     * @return mixed
     */
    function moveFile();


    /**
     * API返回数据
     */
    function apiReturn();

    /**
     * 创建文件夹
     * @return mixed
     */
    function touchDir();

}