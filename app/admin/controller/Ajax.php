<?php

namespace app\admin\controller;

use think\Exception;
use think\exception\FileException;
use app\common\library\Upload;
use app\common\controller\Backend;

class Ajax extends Backend
{
    public function upload()
    {
        $file = $this->request->file('file');
        try {
            $upload                = new Upload($file);
            $attachment            = $upload->upload(null, $this->auth->id);
            $attachment['fullurl'] = full_url($attachment['url']);
            unset($attachment['createtime'], $attachment['quote']);
        } catch (Exception | FileException $e) {
            $this->error($e->getMessage());
        }

        $this->success(__('File uploaded successfully'), [
            'file' => $attachment
        ]);
    }
}