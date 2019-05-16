<?php

namespace App\Services;

use App\Models\ActionLog;
use App\Models\Picture;
use App\Models\File;
use App\Models\Category;
use App\Models\Config;
use App\Models\Sms;
use App\Models\Wechat;
use App\Models\GroupbuyShop;
use App\Models\Merchant;
use App\Models\Printer;
use App\User;
use Flc\Alidayu\Client;
use Flc\Alidayu\App;
use Flc\Alidayu\Requests\AlibabaAliqinFcSmsNumSend;
use Flc\Alidayu\Requests\IRequest;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Mail;
use Excel;
use Cache;
use DB;

class Helper
{
    /**
    * 错误返回数据
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function error($msg,$url = '')
    {
        $result['msg'] = $msg;
        $result['status'] = 'error';
        return $result;
    }

    /**
    * 成功返回数据
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function success($msg,$data = '',$status = 'success')
    {
        $result['msg'] = $msg;
        $result['data'] = $data;
        $result['status'] = $status;
        return $result;
    }

    /**
    * 创建uuid,系统内唯一标识符
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function createUuid()
    {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtolower(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12);
        return $uuid;
    }

    /**
     * 生成随机位数
     * @param  integer $len 长度
     * @return string
     */
    static function makeRand($len = 6,$string = false)
    {
        if($string) {
            $seed = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        } else {
            $seed = '0123456789';
        }

        return substr(str_shuffle(str_repeat($seed, $len)), 0, $len);
    }


    /**
    * 获取目录列表
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function getDir(& $dir)
    {  
        $dirArray = [];
        if(is_dir($dir)) {
            if(false != ($handle = opendir($dir))) {
                while(false != ($file = readdir($handle))) {
                    if($file!='.' && $file!='..' && !strpos($file,'.')) {
                        $dirArray[] = $file;  
                    }
                }
                closedir($handle);  
            }
        }else{
            return 'error';
        }
        return $dirArray;
    }
  
    /**
    * 获取文件列表
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function getFileLists(& $dir)
    {
        $fileArray = [];
        if(is_dir($dir)) {  
            if(false != ($handle = opendir($dir))) {  
                while(false != ($file = readdir($handle))){  
                    if($file!='.' && $file!='..' && strpos($file,'.')) {  
                        $fileArray[] = $file;  
                    }  
                }  
                closedir( $handle );  
            }  
        } else {  
            return 'error';
        }  
        return $fileArray;  
    }  

    /**
    * 获取文件Mime
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function detectFileMimeType($fileName='')
    {
        if(!function_exists('mime_content_type')) {
            $mimeTypes = array(
                'txt' => 'text/plain',
                'htm' => 'text/html',
                'html' => 'text/html',
                'php' => 'text/html',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'xml' => 'application/xml',
                'swf' => 'application/x-shockwave-flash',
                'flv' => 'video/x-flv',

                // images
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                'bmp' => 'image/bmp',
                'ico' => 'image/vnd.microsoft.icon',
                'tiff' => 'image/tiff',
                'tif' => 'image/tiff',
                'svg' => 'image/svg+xml',
                'svgz' => 'image/svg+xml',

                // archives
                'zip' => 'application/zip',
                'rar' => 'application/x-rar-compressed',
                'exe' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'cab' => 'application/vnd.ms-cab-compressed',

                // audio/video
                'mp3' => 'audio/mpeg',
                'qt' => 'video/quicktime',
                'mov' => 'video/quicktime',

                // adobe
                'pdf' => 'application/pdf',
                'psd' => 'image/vnd.adobe.photoshop',
                'ai' => 'application/postscript',
                'eps' => 'application/postscript',
                'ps' => 'application/postscript',

                // ms office
                'doc' => 'application/msword',
                'rtf' => 'application/rtf',
                'xls' => 'application/vnd.ms-excel',
                'ppt' => 'application/vnd.ms-powerpoint',

                // open office
                'odt' => 'application/vnd.oasis.opendocument.text',
                'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            );
            $fileExt = explode('.',$fileName);
            $ext = strtolower(array_pop($fileExt));
            if (array_key_exists($ext, $mimeTypes)) {
                return $mimeTypes[$ext];
            } elseif (function_exists('finfo_open')) {
                $fileInfo = finfo_open(FILEINFO_MIME);
                $mimeType = finfo_file($fileInfo, $fileName);
                finfo_close($fileInfo);
                return $mimeType;
            } else {
                return 'application/octet-stream';
            }
        } else {
            return mime_content_type($fileName);
        }
    }
  
    /**
    * 获取目录/文件列表 
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function getDirFileLists(& $dir)
    {  
        if(is_dir($dir)){  
            $dirFileArray['dirList'] = self::getDir($dir);  
            if($dirFileArray) {  
                foreach($dirFileArray['dirList'] as $handle){  
                    $file = $dir.DIRECTORY_SEPARATOR.$handle;  
                    $dirFileArray['fileList'][$handle] = self::getFileLists($file);  
                }  
            }  
        } else {  
            return 'error';
        }  
        return $dirFileArray;  
    }

    /**
    * 循环删除目录和文件函数
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function delDirAndFile($dirPath)
    {
        if(is_file($dirPath)) {
            $result = unlink($dirPath);
        } else {
            if ($handle = opendir($dirPath)) {
                while (false !== ($item = readdir($handle))) {
                    if ($item != "." && $item != "..") {
                        if (is_dir("$dirPath/$item")) {
                            self::delDirAndFile("$dirPath/$item");
                        } else {
                            if(!unlink("$dirPath/$item")) {
                                return 'error';
                            }
                        }
                    }
                }
                closedir($handle);
                if (!rmdir($dirPath)) {
                    return 'error';
                }
            }
        }
    }

    /**
    * 循环删除文件并不删除文件夹
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function delFile($dirPath)
    {
        if(is_file($dirPath)) {
            $result = unlink($dirPath);
        } else {
            if ($handle = opendir($dirPath)) {
                while (false !== ($item = readdir($handle))) {
                    if ($item != "." && $item != "..") {
                        if (is_dir("$dirPath/$item")) {
                            self::delFile("$dirPath/$item");
                        } else {
                            if(!unlink("$dirPath/$item")) {
                                return 'error';
                            }
                        }
                    }
                }
                closedir($handle);
            }
        }
    }

    /**
    * 创建文件夹
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function makeDir($dirPath)
    {
        if (!is_dir($dirPath)) {
            $result = mkdir($dirPath,0777,true);
            if ($result) {
                return 'success';
            } else {
                return 'error';
            }
        } else {
            return 'error';
        }
    }

    /**
    * 判断文件夹是否为空
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function isEmptyDir($path)
    {    
        $handler = @opendir($path);
        $i=0;
        while($_file=readdir($handler)){
            $i++;
        }
        closedir($handler);
        if($i>2) {
            return false;
        } else {
            return true;  //文件夹为空
        }
    }

    /**
    * 复制文件到文件夹
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function copyFileToDir($sourceFile, $dir)
    {
        if(is_dir($sourceFile)){ // 如果你希望同样移动目录里的文件夹
            return self::copyDirToDir($sourceFile, $dir);
        }
        if(!file_exists($sourceFile)){
            return 'error';
        }
        $filename = basename($sourceFile);
        return copy($sourceFile, $dir .'/'. $filename);
    }

    /**
    * 复制文件夹到文件夹
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function copyDirToDir($sourceDir, $dir)
    {
        if((!is_dir($sourceDir)) || (!is_dir($dir))){
            return 'error';
        }
        // 要复制到新目录
        $newPath = $dir.'/'.basename($sourceDir);
        if(!realpath($newPath)){ // 
            mkdir($newPath);
        }
        foreach(glob($sourceDir.'/*') as $filename)
        {
            self::copyFileToDir($filename, $newPath);
        }
    }

    /**
    * 把返回的数据集转换成Tree
    * @param array $list 要转换的数据集
    * @param string $pid parent标记字段
    * @param string $level level标记字段
    * @return array
    */
    static function listToTree($list, $pk='id',$pid = 'pid',$child = '_child',$root=0) {
        // 创建Tree
        $tree = array();
        if(is_array($list)) {
            // 创建基于主键的数组引用
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] =& $list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] =& $list[$key];
                }else{
                    if (isset($refer[$parentId])) {
                        $parent =& $refer[$parentId];
                        $parent[$child][] =& $list[$key];
                    }
                }
            }
        }
        return $tree;
    }

    /**
    * 把Tree转换为有序列表
    * @return array
    */
    static function treeToOrderList($arr,$level=0,$filed='name',$child='_child') {
        static $tree=array();
        if(is_array($arr)) {
            foreach ($arr as $key=>$val) {
                $val[$filed] = str_repeat('—', $level).$val[$filed];
                $tree[]=$val;
                if (isset($val[$child])) {
                    self::treeToOrderList($val[$child],$level+1,$filed,$child);
                }        
            }
        }
        return $tree;
    }

    /**
    * 判断当前url是否被选中
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function urlSelected($url,$selected = 'active')
    {

        if (!empty($url)) {
            
            $host = $_SERVER['HTTP_HOST'];
            $requestUri = $_SERVER['REQUEST_URI'];

            // https状态 todo
            $httpsStatus = self::config('HTTPS_STATUS');

            $httpsStatus == 'on' ? $baseUrl = 'http:s//' : $baseUrl = 'http://';

            $getUrl = $baseUrl.$host.$requestUri;

            if(!(strpos($getUrl, $url) !== false) || (($requestUri =='/') && ($url == '/home/index/index'))) {
                $selected = '';
            }

        } else {
            $selected = '';
        }

        return $selected;
    }

    /**
     * sms_post Sioo希奥发送手机短信接口
     * @return string 验证码$uid  = '500',$auth = '680d1acc90b4f062'
     */
    static function siooSendSms($phone,$content) {

        if(!preg_match("/^1[34578]\d{9}$/", $phone)) {
            return self::error('手机号错误！');
        }

        $uid = self::config('SIOO_UID');
        $code = self::config('SIOO_CODE');
        $password = self::config('SIOO_PASSWORD');

        if(empty($uid) || empty($code) || empty($password)) {
            return self::error('接口配置错误！');
        }

        // 转换内容类型
        $msg  = mb_convert_encoding($content,'GBK','utf-8');

        // 接口url
        $url = "http://sms.10690221.com:9011/hy/?uid="
        .$uid
        ."&auth=".md5($code.$password)
        ."&mobile=".$phone
        ."&msg=".$msg
        ."&expid=0";

        $result = self::curl($url);

        if ($result>=0) {
            return self::success('发送成功！');
        } else {
            return self::error('发送失败！');
        }
    }

    /**
     * sms_post Alidayu发送手机短信接口
     * string $config = ['app_key' => '*****','app_secret' => '************',// 'sandbox' => true,  // 是否为沙箱环境，默认false;
     * string $signName = '积木云'
     * string $templateCode = 'SMS_70450333'
     * string $phone = '15076569633'
     * string $smsParam = [ 'number' => rand(100000, 999999)]
     */
    static function alidayuSendSms($templateCode,$phone,$smsParam) {

        if(!preg_match("/^1[34578]\d{9}$/", $phone)) {
            return self::error('手机号错误！');
        }

        $config['app_key'] = self::config('ALIDAYU_APP_KEY');
        $config['app_secret'] = self::config('ALIDAYU_APP_SECRET');
        $signName = self::config('ALIDAYU_APP_SIGNNAME');

        if(empty($config['app_key']) || empty($config['app_secret']) || empty($signName)) {
            return self::error('接口配置错误！');
        }

        if(empty($templateCode)) {
            return self::error('模板代码不能为空！');
        }

        if(empty($smsParam)) {
            return self::error('短信参数不能为空！');
        }

        //执行发短信
        $client = new Client(new App($config));
        $request = new AlibabaAliqinFcSmsNumSend;

        $request->setRecNum($phone)
                ->setSmsParam($smsParam)
                ->setSmsFreeSignName($signName)
                ->setSmsTemplateCode($templateCode);

        $result = $client->execute($request);

        if ($result) {
            return self::success('发送成功！');
        } else {
            return self::error('发送失败！');
        }
    }

    /**
    * 将数组里面的状态格式化
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function listsFormat($lists)
    {
        if($lists) {
            foreach ($lists as $key => $value) {
                switch ($value['status']) {
                    case -1:
                        $lists[$key]['status'] = '已删除';
                        break;
                    case 1:
                        $lists[$key]['status'] = '正常';
                        break;
                    case 2:
                        $lists[$key]['status'] = '已禁用';
                        break;
                    case 3:
                        $lists[$key]['status'] = '待审核';
                        break;
                    default:
                        $lists[$key]['status'] = '未知';
                        break;
                }
            }
        }

        return $lists;
    }

    /**
     * 生成缩略图
     * @author tangtanglove
     * @param string $imagePath 图片路径
     * @param string $thumbPath 缩略图路径
     */
    static function createThumb($imagePath,$thumbPath,$width,$height,$thumbType = 1)
    {
        if (empty($imagePath)) {
            return self::error('图片路径不能为空！');
        }

        if (empty($thumbPath)) {
            //如果不定义缩略图路径，则以thumb_+原图片名命名
            $list = explode('/', $imagePath);
            $key = count($list)-1;
            //定义缩略图名称
            $thumb_name = 'thumb_'.$width.'_'.$height.'_'.$list[$key];
            $thumbPath = str_replace($list[$key],'',$imagePath).$thumb_name;
        }

        if (is_file($imagePath)) {
            //不存在缩略图则创建
            if (!is_file($thumbPath)) {
                $image = \think\Image::open($imagePath);
                $image->thumb($width, $height,$thumbType)->save($thumbPath);
            }
            return $thumbPath;
        }else{
            return $imagePath;
        }
    }

    /**
    * 适应手机页面
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function mobileAdaptor($objects)
    {
        if($objects) {
            foreach ($objects as $key => $object) {

                if(isset($object['content'])) {
                    $preg_str = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";                    
                    preg_match_all($preg_str,$object['content'],$match);
                    
                    foreach ($match[1] as $key1 => $value) {
                        if(!strstr($value,"v.qq.com")) {
                            if(strpos($value,'../') !== false) {
                                $objects[$key]['cover_path'.$key1] = 'http://'.$_SERVER['HTTP_HOST'].'/'.str_replace('../','',$value);
                                if(self::config('HTTPS_STATUS') == 'on') {
                                    $objects[$key]['cover_path'.$key1] = 'https://'.$_SERVER['HTTP_HOST'].'/'.str_replace('../','',$value);
                                }
                            } else {
                                $objects[$key]['cover_path'.$key1] = $value;
                            }
                        }
                    }

                    $preg_str1 = "/<[video|VIDEO|embed|EMBED|source|SOURCE].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
                    preg_match_all($preg_str1,$object['content'],$match1);
                    
                    foreach ($match1[1] as $key2 => $value2) {
                        if(strstr($value2,"v.qq.com")) {
                            $objects[$key]['video_path'.$key2] = $value2;
                        }
                    }
                }

                // 封面图
                if (isset($object['cover_id'])) {
                    // 获取文件url，用于外部访问
                    $objects[$key]['cover_path'] = self::getPicture($object['cover_id']);
                }

                // 多封面
                if (isset($object['cover_ids'])) {
                    // 获取文件url，用于外部访问
                    if(count(explode('[',$object['cover_ids']))>1) {
                        $coverIds = json_decode($object['cover_ids'], true);

                        foreach($coverIds as $coverKey => $coverId) {
                            $objects[$key]['cover_path'.$coverKey] = self::getPicture($coverId);
                        }
                    }
                }
            }

            // 生成手机图
            if (isset($objects['content'])) {
                $preg_str = "/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/";
                preg_match_all($preg_str,$objects['content'],$match);

                if($match[1]) {
                    foreach ($match[1] as $key => $value) {
                        if(strpos($value,'../') !== false) {
                            $objects['content'] = str_replace($value,'http://'.$_SERVER['HTTP_HOST'].'/'.str_replace('../','',$value),$objects['content']);

                            if(self::config('HTTPS_STATUS') == 'on') {
                                $objects['content'] = str_replace($value,'https://'.$_SERVER['HTTP_HOST'].'/'.str_replace('../','',$value),$objects['content']);
                            }
                        }
                    }
                }

                $preg_str1 = "/<[video|VIDEO|embed|EMBED|source|SOURCE].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/";
                preg_match_all($preg_str1,$objects['content'],$match1);
                
                foreach ($match1[1] as $key2 => $value2) {
                    $objects['video_path'.$key2] = $value2;
                }
            }

            // 多封面
            if (isset($objects['cover_ids']) && !empty($objects['cover_ids'])) {

                // 获取文件url，用于外部访问
                if(count(explode('[',$objects['cover_ids']))>1) {
                    $coverIds = json_decode($objects['cover_ids'], true);
                }

                if($coverIds) {
                    foreach($coverIds as $coverKey => $coverId) {
                        $url = self::getPicture($coverId);
                        $objects['cover_path'.$coverKey] = $url;
                    }
                }
            }

            // 单图
            if (isset($objects['cover_id']) && !empty($objects['cover_id'])) {

                // 获取文件url，用于外部访问
                $objects['cover_path'] = self::getPicture($objects['cover_id']);
            }
        }

        return $objects;
    }

    /**
    * 记录日志
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function actionLog($data)
    {
        if (empty($data['url'])) {
            $data['url'] = $_SERVER['REQUEST_URI'];
        }
        
        if (!empty(auth()->user())) {
            $data['object_id'] = auth()->user()->id;
        }

        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        ActionLog::create($data);
    }

    /**
    * 获取文章内视频
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function getContentVideo($content)
    {
        preg_match_all('/<[iframe|video|embed]*\s+src="([^"]*)"[^>]*>/is',$content,$match);
        return $match[1][0];
    }

    /**
    * 获取文章内图片url
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function getContentPicture($content)
    {
        preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.png]))[\'|\"].*?[\/]?>/",$content,$match);

        $result = '';

        foreach ($match[1] as $key => $value) {
            if(strpos($value,'../') !== false) {
                $baseUrl = 'http://';

                if (self::config('HTTPS_STATUS') == 'on') {
                    $baseUrl = 'https://';
                }

                $result[$key] = $baseUrl.$_SERVER['HTTP_HOST'].'/'.str_replace('../','',$value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
    * 获取图片url
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function getPicture($id,$key=0,$field='path')
    {
        // 本身为图片地址，直接返回
        if(strpos($id,'http') !== false) {
            return $id;
        }

        // 获取文件url，用于外部访问
        if(count(explode('[',$id))>1) {
            $ids = json_decode($id, true);
            if(isset($ids[$key])) {
                $id = $ids[$key];
            } else {
                return '//'.$_SERVER['HTTP_HOST'].'/images/default.png';
            }
        }

        $picture = Picture::where('id',$id)->first();

        // 图片存在
        if(!empty($picture)) {
            if ($field == 'path') {

                // 存在http，本身为图片地址
                if(strpos($picture['path'],'http') !== false) {
                    $url = $picture['path'];
                } else {
                    $baseUrl = 'http://';
                    if(self::config('HTTPS_STATUS') == 'on') {
                        $baseUrl = 'https://';
                    }

                    $url = $baseUrl.$_SERVER['HTTP_HOST'].'/'.Storage::url($picture['path']);
                }

                $result = $url;
            } else {
                $result = $picture[$field];
            }

            return $result;
        }
        
        return '//'.$_SERVER['HTTP_HOST'].'/images/default.png';
    }

    /**
    * 获取文件
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function getFile($id,$field='path')
    {
        $file = File::where('id',$id)->first();
        if(!empty($file)) {
            $file = $file->toArray();
            if ($field == 'path') {
                if(strpos($file['path'],'http') !==false) {
                    return $file['path'];
                } else {
                    $baseUrl = 'http://';

                    if(self::config('HTTPS_STATUS') == 'on') {
                        $baseUrl = 'https://';
                    }

                    return $baseUrl.$_SERVER['HTTP_HOST'].Storage::url($file['path']);
                }
            } else {
                return $file[$field];
            }
        }
    }

    /**
    * 获取分类名称
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function getCategory($id)
    {
        $category = Category::find($id);
        if(!empty($category)) {
            return $category->title;
        }
    }

    /**
    * 解析属性条件
    * @author tangtanglove <dai_hang_love@126.com>
    */
	static function parseAttrOption($name,$option,$value,$type)
    {
        $string = '';
        switch ($type) {
            case 'select':
                $optionArr = explode("\n",$option);
                if (!empty($optionArr)) {
                    $stringHeader = '<select name="'.$name.'" lay-verify="required">';
                    $stringText = '';
                    $selected = '';
                    foreach ($optionArr as $optionKey => $optionValue) {
                        if ($optionValue==$value) {
                            $selected = 'selected';
                        } else {
                            $selected = '';
                        }
                        $stringText = $stringText.'<option value="'.$optionValue.'" '.$selected.'>'.$optionValue.'</option>';
                    }
                    $stringFooter = '</select>';
                }
                $string = $stringHeader.$stringText.$stringFooter;
                break;
            
            default:
                # code...
                break;
        }

        return $string;
    }

    /**
    * 强制清除缓存
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function clearCache()
    {
        self::delDirAndFile(storage_path('framework/cache/data/'));
        self::delFile(storage_path('framework/views/'));
    }

    /**
     * 字符串截取，支持中文和其他编码
     * static 
     * access public
     * @param string $str 需要转换的字符串
     * @param string $start 开始位置
     * @param string $length 截取长度
     * @param string $charset 编码格式
     * @param string $suffix 截断显示字符
     * return string
     */
    static function msubstr($str, $start=0, $length, $charset="utf-8")
    {
        if(function_exists("mb_substr")) {
            $slice = mb_substr($str, $start, $length, $charset);
        } elseif(function_exists('iconv_substr')) {
            $slice = iconv_substr($str,$start,$length,$charset);
            if(false === $slice) {
                $slice = '';
            }
        } else {
            $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
            $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
            $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
            $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
            preg_match_all($re[$charset], $str, $match);
            $slice = join("",array_slice($match[0], $start, $length));
        }

        $strlen=mb_strlen($str);
        if($strlen>$length) {
            $slice = $slice.'...';
        }
        return $slice;
    }

    /**
    * 过滤Emoji
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function filterEmoji($str)
    {
        $str = preg_replace_callback(
        '/./u',
        function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        },
        $str);
        return $str;
    }

    // 返回公众号配置
    static function wechatConfig($id = '')
    {
        if(empty($id)) {
            $wechatId = Session::get('wechat_id');
        } else {
            $wechatId = $id;
        }

        if(!empty($wechatId)) {
            $wechat = Wechat::find($wechatId);
        } else {
            $wechat = Wechat::where('status',1)->first();
        }

        if(empty($wechat)) {
            return self::error('无此公众号配置！');
        }

        $config = [
            'debug'     => true,
            'app_id'    => $wechat['appid'],
            'secret'    => $wechat['secret'],
            'token'     => $wechat['token'],
            'aes_key'   => $wechat['aes_key'],
            'oauth' => [
                'scopes'   => ['snsapi_userinfo'],
                'callback' => url('wechat/auth/callback'),
            ],
            'log' => [
                'level' => 'debug',
                'file'  => storage_path('/logs/easywechat/easywechat_'.date('Ymd').'.log'),
            ]
        ];

        return $config;
    }

    // 返回微信支付配置
    static function wechatPayConfig()
    {
        $getApiclientCertPath = '';
        $getApiclientKeyPath = '';

        $getApiclientCert = self::config('WECHAT_PAY_APICLIENT_CERT');
        $getApiclientKey  = self::config('WECHAT_PAY_APICLIENT_KEY');

        if(!empty($getApiclientCert) && !empty($getApiclientKey)) {
            $apiclientCertInfo = File::where('id',$getApiclientCert)->first();
            $apiclientKeyInfo = File::where('id',$getApiclientKey)->first();
            $getApiclientCertPath = str_replace("\\","/",storage_path('app\\'.$apiclientCertInfo['path']));
            $getApiclientKeyPath = str_replace("\\","/",storage_path('app\\'.$apiclientKeyInfo['path']));
        }

        $config = [
            'debug'     => true,
            'app_id'    => self::config('WECHAT_PAY_APP_ID'),
            'log' => [
                'level' => 'debug',
                'file'  => storage_path('/logs/easywechat/easywechat_'.date('Ymd').'.log'),
            ],
            'mch_id'            => self::config('WECHAT_PAY_MERCHANTID'),
            'key'                => self::config('WECHAT_PAY_KEY'),
            'cert_path'          => $getApiclientCertPath, // XXX: 绝对路径！！！！
            'key_path'           => $getApiclientKeyPath // XXX: 绝对路径！！！！
        ];

        return $config;
    }

    // 返回微信app支付配置
    static function wechatAppPayConfig()
    {
        $getApiclientCertPath = '';
        $getApiclientKeyPath = '';

        $getApiclientCert = self::config('WECHAT_APP_PAY_APICLIENT_CERT');
        $getApiclientKey  = self::config('WECHAT_APP_PAY_APICLIENT_KEY');

        if(!empty($getApiclientCert) && !empty($getApiclientKey)) {
            $apiclientCertInfo = File::where('id',$getApiclientCert)->first();
            $apiclientKeyInfo = File::where('id',$getApiclientKey)->first();
            $getApiclientCertPath = str_replace("\\","/",storage_path('app\\'.$apiclientCertInfo['path']));
            $getApiclientKeyPath = str_replace("\\","/",storage_path('app\\'.$apiclientKeyInfo['path']));
        }

        $config = [
            'debug'     => true,
            'app_id'    => self::config('WECHAT_APP_PAY_APP_ID'),
            'log' => [
                'level' => 'debug',
                'file'  => storage_path('/logs/easywechat/easywechat_'.date('Ymd').'.log'),
            ],
            'mch_id'             => self::config('WECHAT_APP_PAY_MERCHANTID'),
            'key'                => self::config('WECHAT_APP_PAY_KEY'),
            'cert_path'          => $getApiclientCertPath, // XXX: 绝对路径！！！！
            'key_path'           => $getApiclientKeyPath // XXX: 绝对路径！！！！
        ];

        return $config;
    }

    // 返回微信小程序支付配置--限号查询小程序
    static function wechatMiniProgramPayConfig()
    {
        $getApiclientCertPath = '';
        $getApiclientKeyPath = '';

        $getApiclientCert = self::config('WECHAT_MINIPROGRAMPAY_APICLIENT_CERT');
        $getApiclientKey  = self::config('WECHAT_MINIPROGRAMPAY_APICLIENT_KEY');

        if(!empty($getApiclientCert) && !empty($getApiclientKey)) {
            $apiclientCertInfo = File::where('id',$getApiclientCert)->first();
            $apiclientKeyInfo = File::where('id',$getApiclientKey)->first();
            $getApiclientCertPath = str_replace("\\","/",storage_path('app\\'.$apiclientCertInfo['path']));
            $getApiclientKeyPath = str_replace("\\","/",storage_path('app\\'.$apiclientKeyInfo['path']));
        }

        $config = [
            'debug'     => true,
            'app_id'    => self::config('WECHAT_MINIPROGRAMPAY_APP_ID'),
            'log' => [
                'level' => 'debug',
                'file'  => storage_path('/logs/easywechat/easywechat_'.date('Ymd').'.log'),
            ],
            'mch_id'             => self::config('WECHAT_MINIPROGRAMPAY_MERCHANTID'),
            'key'                => self::config('WECHAT_MINIPROGRAMPAY_KEY'),
            'secret'             => self::config('WECHAT_MINIPROGRAMPAY_SECRET'),
            'cert_path'          => $getApiclientCertPath, // XXX: 绝对路径！！！！
            'key_path'           => $getApiclientKeyPath // XXX: 绝对路径！！！！
        ];

        return $config;
    }

    /**
    * 创建订单号
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function createOrderNo()
    {
        return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
    * 判断是否为手机端
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function isMobile()
    {
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array ('nokia',  'sony','ericsson','mot',
                'samsung','htc','sgh','lg','sharp',
                'sie-','philips','panasonic','alcatel',
                'lenovo','iphone','ipod','blackberry',
                'meizu','android','netfront','symbian',
                'ucweb','windowsce','palm','operamini',
                'operamobi','openwave','nexusone','cldc',
                'midp','wap','mobile'
                );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字  
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $url 请求网址
     * @param bool $params 请求参数
     * @param int $ispost 请求方式
     * @param bool $headers 请求头部
     * @param int $https https协议
     * @return bool|mixed
     */
    static function curl($url, $params = false, $method = 'get', $headers = false, $https = 0)
    {
        $httpInfo = array();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($https) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        }

        if ($method == 'post') {
            curl_setopt($ch, CURLOPT_POST, true);

            if (is_array($params)) {
                $params = http_build_query($params);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            }
            
            curl_setopt($ch, CURLOPT_URL, $url);
        } else {
            if ($params) {
                if (is_array($params)) {
                    $params = http_build_query($params);
                }
                curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
            } else {
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }

        $response = curl_exec($ch);

        if ($response === FALSE) {
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        curl_close($ch);
        return $response;
    }

    /**
    * 获取用户名
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function user($uid = '',$field = 'name')
    {
        $result = '';
        if(empty($uid)) {
            $user = auth('web')->user();
        } else {
            $user = User::where('id',$uid)->first();
        }

        if(isset($user[$field])) {
            $result = $user[$field];
        }

        return $result;
    }

    /**
    * 获取网站配置信息
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function config($name)
    {
        $config = Config::where('name',$name)->first();
        $value = '';
        if(!empty($config)) {
            $value = $config->value;
        }
        return $value;
    }

    /**
    * 生成二维码
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function createQrCode($text)
    {
        $qrCode = new QrCode($text);
        
        header('Content-Type: '.$qrCode->getContentType());
        echo $qrCode->writeString();
        
        exit;
    }

    /**
    * 发送邮件
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function sendEmail($subject,$toEmail,$content)
    {
        config([
            'mail.host' => self::config('EMAIL_HOST'),
            'mail.port' => self::config('EMAIL_PORT'),
            'mail.from' => ['address' => self::config('EMAIL_USERNAME'),'name' => self::config('WEB_SITE_NAME')],
            'mail.username' => self::config('EMAIL_USERNAME'),
            'mail.password' => self::config('EMAIL_PASSWORD'),
            ]);
        Mail::raw($content, function ($message) use($toEmail, $subject) {
            $message ->to($toEmail)->subject($subject);
        });

        if(count(Mail::failures()) < 1){
            return true;
        }else{
            return false;
        }
    }

    /**
    * 把 null转换为空''
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function unsetNull($data)
    {
        $result = json_decode(str_replace(':null', ':""', json_encode($data)),true);
        if($result) {
            $data = $result;
        }
        return $data;
    }

    /**
    * 是否微信浏览器
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function isWechat()
    {
        // 微信中登录认证
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) { 
            return true;
        } else {
            return false;
        }
    }

    /**
    * 导出Excel
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function export($fileName,$title,$lists)
    {
        Excel::create($fileName.'_'.date('YmdHis'),function($excel) use ($fileName,$title,$lists) {
            $excel->sheet($fileName, function($sheet) use ($title,$lists) {
                $sheet->setAutoSize(true);
                $sheet->prependRow($title);
                $sheet->rows($lists);
            });
        })->export('xls');
    }

    /**
    * 导入Excel
    * @author tangtanglove <dai_hang_love@126.com>
    */
    static function import($fileId)
    {
        $file = File::where('id',$fileId)->first();

        Excel::load(storage_path('app/').$file->path, function($reader) use (&$results) {
            $reader = $reader->getSheet(0);//excel第一张sheet
            $results = $reader->toArray();
        });

        unset($results[0]);//去除表头
        return $results;
    }

    static function clientIp()
    {
        $keys = array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
        foreach ($keys as $key) {
            if ( ! isset($_SERVER[$key])) {
                continue;
            }
            $ip = \array_filter(\explode(',', $_SERVER[$key]));
            $ip = \filter_var(\end($ip), FILTER_VALIDATE_IP);
            if ($ip) {
                return $ip;
            }
        }
        return '';
    }

    static function getCpuUsage()
    {
        static $cpu = null;
        if (null !== $cpu) {
            return $cpu;
        }
        if (self::isWin()) {
            $cpu = self::getWinCpuUsage();
            return $cpu;
        }
        $filePath = ('/proc/stat');
        if ( ! \is_readable($filePath)) {
            $cpu = array();
            return $cpu;
        }
        $stat1 = \file($filePath);
        \sleep(1);
        $stat2       = \file($filePath);
        $info1       = \explode(' ', \preg_replace('!cpu +!', '', $stat1[0]));
        $info2       = \explode(' ', \preg_replace('!cpu +!', '', $stat2[0]));
        $dif         = array();
        $dif['user'] = $info2[0] - $info1[0];
        $dif['nice'] = $info2[1] - $info1[1];
        $dif['sys']  = $info2[2] - $info1[2];
        $dif['idle'] = $info2[3] - $info1[3];
        $total       = \array_sum($dif);
        $cpu         = array();
        foreach ($dif as $x => $y) {
            $cpu[$x] = \round($y / $total * 100, 1);
        }
        return $cpu;
    }

    static function getHumanCpuUsage()
    {
        $cpu = self::getCpuUsage();
        return $cpu ?: array();
    }


    static function getMemoryUsage($key)
    {
        $key = \ucfirst($key);
        if (self::isWin()) {
            return 0;
        }
        static $memInfo = null;
        if (null === $memInfo) {
            $memInfoFile = '/proc/meminfo';
            if ( ! \is_readable($memInfoFile)) {
                $memInfo = 0;
                return 0;
            }
            $memInfo = \file_get_contents($memInfoFile);
            $memInfo = \str_replace(array(
                ' kB',
                '  ',
            ), '', $memInfo);
            $lines = array();
            foreach (\explode("\n", $memInfo) as $line) {
                if ( ! $line) {
                    continue;
                }
                $line            = \explode(':', $line);
                $lines[$line[0]] = (int) $line[1];
            }
            $memInfo = $lines;
        }

        switch ($key) {
            case 'MemRealUsage':
                $memAvailable = 0;
                if (isset($memInfo['MemAvailable'])) {
                    $memAvailable = $memInfo['MemAvailable'];
                } elseif (isset($memInfo['MemFree'])) {
                    $memAvailable = $memInfo['MemFree'];
                }
                return $memInfo['MemTotal'] - $memAvailable;
            case 'SwapRealUsage':
                if ( ! isset($memInfo['SwapTotal']) || ! isset($memInfo['SwapFree']) || ! isset($memInfo['SwapCached'])) {
                    return 0;
                }
                return $memInfo['SwapTotal'] - $memInfo['SwapFree'] - $memInfo['SwapCached'];
        }
        return isset($memInfo[$key]) ? (int) $memInfo[$key] : 0;
    }

    static function formatBytes($bytes, $precision = 2)
    {
        if ( ! $bytes) {
            return 0;
        }
        $base     = \log($bytes, 1024);
        $suffixes = array('', ' K', ' M', ' G', ' T');
        return \round(\pow(1024, $base - \floor($base)), $precision) . $suffixes[\floor($base)];
    }

    static function getHumamMemUsage($key)
    {
        return self::formatBytes(self::getMemoryUsage($key) * 1024);
    }

    // 获取当前地理位置
    static function address($ip='', $latitude='', $longitude='') {

        $getAddress = [];
        if(!empty($ip)) {
            // 根据ip获取地理位置
            $address = self::curl('http://ip.taobao.com/service/getIpInfo.php?ip='.$ip);
            $address = json_decode($address,true);
            if($address === false) {
                $getAddress = '';
            } else {
                $getAddress['country'] = $address['data']['country'];
                $getAddress['province'] = $address['data']['region'];
                $getAddress['city'] = $address['data']['city'];
                $getAddress['district'] = $address['data']['county'];
            }
        } elseif(!empty($latitude) && !empty($longitude)) {
            // 根据经纬度获取地理位置
            $address = self::curl('http://apis.map.qq.com/jsapi?qt=rgeoc&lnglat='.$longitude.'%2C'.$latitude);
            $address = mb_convert_encoding($address, "utf-8", "gb18030");
            $address = json_decode($address,true);
            if($address === false) {
                $getAddress = '';
            } else {
                $getAddress['country'] = $address['detail']['results'][0]['n'];
                $getAddress['province'] = $address['detail']['results'][0]['p'];
                $getAddress['city'] = $address['detail']['results'][0]['c'];
                $getAddress['district'] = $address['detail']['results'][1]['address_name'];
            }
        }

        return $getAddress;
    }

    /**
     * 验证短信验证码是否合法
     * @return string
     */
    static function validateSmsCode($phone,$code) {

        if(empty($phone)) {
            return self::error('请先获取手机验证码！');
        }

        if(empty($code)) {
            return self::error('手机验证码不能为空！');
        }

        $sms = Sms::where('phone',$phone)->orderBy('id','desc')->first();

        // 判断验证码是否正确
        if($sms['code'] != $code) {

            // 更新错误次数
            Sms::where('id',$sms['id'])->increment('error_times');
            return self::error('手机验证码错误！');
        }

        // 验证码有效时间6分钟，最多允许6次错误
        if(((time() - strtotime($sms['created_at'])) > 3600) || ($sms['error_times'])>6) {
            return self::error('手机验证码已经失效，请重新获取！');
        }

        return self::success('验证成功！');
    }

    /**
     * 获取Token
     * @param $grantType
     * @param $scope
     * @param $timesTamp
     * @param null $code
     * @return mixed
     */
    public static function getToken($clientId,$clientSecret,$grantType, $scope, $timesTamp, $code = null)
    {
        $requestAll = [
            'client_id' => $clientId,
            'sign' => md5($clientId.$timesTamp.$clientSecret),
            'id' => self::createUuid(),
            'grant_type' => $grantType,
            'scope' => $scope,
            'code' => $code,
            'timestamp' => $timesTamp,
        ];
        
        $url = 'https://open-api.10ss.net/oauth/oauth';
        $params = http_build_query($requestAll);
        return self::curl($url, $params, $ispost = 1, $https = 0);
    }

    /**
     * 刷新Token
     * @param $grantType
     * @param $scope
     * @param $timesTamp
     * @param $RefreshToken
     * @return mixed
     */
    public static function refreshToken($clientId,$clientSecret,$grantType, $scope, $timesTamp, $RefreshToken)
    {
        $requestAll = [
            'client_id' => $clientId,
            'sign' => md5($clientId.$timesTamp.$clientSecret),
            'id' => self::createUuid(),
            'grant_type' => $grantType,
            'scope' => $scope,
            'refresh_token' => $RefreshToken,
            'timestamp' => $timesTamp,
        ];

        $url = 'https://open-api.10ss.net/oauth/oauth';
        $params = http_build_query($requestAll);
        return self::curl($url, $params, $ispost = 1, $https = 0);
    }

    /**
     * 打印接口
     * @param $machineCode
     * @param $accessToken
     * @param $content
     * @param $originId
     * @param $timesTamp
     * @return mixed
     */
    public static function printer($clientId,$clientSecret,$machineCode, $accessToken, $content, $originId, $timesTamp)
    {
        $url = 'https://open-api.10ss.net/print/index';
        $requestAll = [
            'client_id' => $clientId,
            'sign' => md5($clientId.$timesTamp.$clientSecret),
            'id' => self::createUuid(),
            'machine_code' => $machineCode,
            'access_token' => $accessToken,
            'content' => $content,
            'origin_id' => $originId,
            'timestamp' => $timesTamp,
        ];
        $params = http_build_query($requestAll);
        return self::curl($url, $params, $ispost = 1, $https = 0);;
    }

    /**
     * 关机重启接口
     * @param $machineCode
     * @param $accessToken
     * @param $responseType
     * @param $timesTamp
     * @return mixed
     */
    public static function shutdownRestart($clientId,$clientSecret,$machineCode, $accessToken, $responseType, $timesTamp)
    {
        $url = 'https://open-api.10ss.net/printer/shutdownrestart';
        $requestAll = [
            'client_id' => $clientId,
            'sign' => md5($clientId.$timesTamp.$clientSecret),
            'id' => self::createUuid(),
            'machine_code' => $machineCode,
            'access_token' => $accessToken,
            'response_type' => $responseType,
            'timestamp' => $timesTamp,
        ];
        $params = http_build_query($requestAll);
        return self::curl($url, $params, $ispost = 1, $https = 0);;
    }

    /**
     * 商户打印机
     * @param $shopId 商家id
     * @param $originId 可以为商家订单号的id
     * @param $content 打印内容
     * @return mixed
     */
    public static function mchPrinter($mchId,$originId,$content)
    {

        $mchInfo = Merchant::where('id',$mchId)->first();
        $printer = Printer::where('mch_id',$mchInfo['id'])->first();

        if(empty($printer)) {
            return self::error('无此打印机配置信息！');
        }

        $machineCode    = $printer['machine_code'];
        $clientId       = $printer['client_id'];
        $clientSecret   = $printer['client_secret'];
        $accessToken    = $printer['access_token'];
        $refreshToken   = $printer['refresh_token'];
        $grantType      = 'client_credentials';  //自有模式(client_credentials) || 开放模式(authorization_code)
        $scope          = 'all';                 //权限
        $timesTamp      = time();                //当前服务器时间戳(10位)

        $getYlyAccessToken = Cache::get('yly_access_token');

        if(empty($getYlyAccessToken)) {

            // 获取access_token
            $tokenInfo = Helper::getToken($clientId,$clientSecret,$grantType,$scope,$timesTamp);
            $tokenInfo = json_decode($tokenInfo,true);

            $data['access_token'] = $tokenInfo['body']['access_token'];
            $data['refresh_token'] = $tokenInfo['body']['refresh_token'];

            // 储存到缓存
            Cache::put('yly_access_token', $data, $tokenInfo['body']['expires_in']/60);

            // 赋值
            $accessToken = $tokenInfo['body']['access_token'];
        } else {
            // 赋值
            $accessToken = $getYlyAccessToken['access_token'];
        }

        $result = self::printer($clientId,$clientSecret,$machineCode, $accessToken, $content, $originId, $timesTamp);
        $result = json_decode($result,true);

        if ($result['error'] == 0) {
            return self::success('操作成功！');
        } else {
            return self::error('操作失败！');
        }
    }

    /**
    +----------------------------------------------------------
    * 将一个字符串部分字符用*替代隐藏
    +----------------------------------------------------------
    * @param string    $string   待转换的字符串
    * @param int       $bengin   起始位置，从0开始计数，当$type=4时，表示左侧保留长度
    * @param int       $len      需要转换成*的字符个数，当$type=4时，表示右侧保留长度
    * @param int       $type     转换类型：0，从左向右隐藏；1，从右向左隐藏；2，从指定字符位置分割前由右向左隐藏；3，从指定字符位置分割后由左向右隐藏；4，保留首末指定字符串
    * @param string    $glue     分割符
    +----------------------------------------------------------
    * @return string   处理后的字符串
    +----------------------------------------------------------
    */
    public static function hideStr($string, $bengin=0, $len = 4, $type = 0, $glue = "@") {
        if (empty($string))
            return false;
        $array = array();
        if ($type == 0 || $type == 1 || $type == 4) {
            $strlen = $length = mb_strlen($string);
            while ($strlen) {
                $array[] = mb_substr($string, 0, 1, "utf8");
                $string = mb_substr($string, 1, $strlen, "utf8");
                $strlen = mb_strlen($string);
            }
        }
        if ($type == 0) {
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", $array);
        }else if ($type == 1) {
            $array = array_reverse($array);
            for ($i = $bengin; $i < ($bengin + $len); $i++) {
                if (isset($array[$i]))
                    $array[$i] = "*";
            }
            $string = implode("", array_reverse($array));
        }else if ($type == 2) {
            $array = explode($glue, $string);
            $array[0] = self::hideStr($array[0], $bengin, $len, 1);
            $string = implode($glue, $array);
        } else if ($type == 3) {
            $array = explode($glue, $string);
            $array[1] = self::hideStr($array[1], $bengin, $len, 0);
            $string = implode($glue, $array);
        } else if ($type == 4) {
            $left = $bengin;
            $right = $len;
            $tem = array();
            for ($i = 0; $i < ($length - $right); $i++) {
                if (isset($array[$i]))
                    $tem[] = $i >= $left ? "*" : $array[$i];
            }
            $array = array_chunk(array_reverse($array), $right);
            $array = array_reverse($array[0]);
            for ($i = 0; $i < $right; $i++) {
                $tem[] = $array[$i];
            }
            $string = implode("", $tem);
        }
        return $string;
    }

    /**
     * 获取表数据统计
     * @return mixed
     */
    public static function getTableDataCount($tableName)
    {
        $count = DB::table($tableName)->where('status',1)->count();
        return $count;
    }

    // 获取手机号区域信息
    static function phoneInfo($phone)
    {
        $host = "https://ali-mobile.showapi.com";
        $path = "/6-1";
        $method = "GET";
        $appcode = self::config('PHONEINFO_APPCODE');
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "num=".$phone;
        $bodys = "";
        $url = $host . $path . "?" . $querys;
    
        $result = self::curl($url);

        return json_decode($result,true);
    }

    // 腾讯云语音识别，$audioFile='http://www.website/1.mp3'，必须安装ffmpeg程序
    static function voiceRecognition($audioFile)
    {
        //用户需要修改为自己腾讯云官网账号中的appid，secretid与secretKey
        $SecretId = self::config('TENCENTCLOUD_SECRET_ID');
        $secretKey = self::config('TENCENTCLOUD_SECRET_KEY');

        // 语音数据来源 0:语音url，1:语音数据bodydata
        $SourceType = 1;

        /**
         * 读取音频文件
         */
        $getFilePaths = explode('/',$audioFile);
        $fileName = $getFilePaths[count($getFilePaths)-1];
        
        $audioContent = file_get_contents($audioFile);

        if ($audioContent == FALSE) {
            return self::error("语音文件不存在！");
        }

        $fileNameWithoutExts = explode('.',$getFilePaths[count($getFilePaths)-1]);

        $fileNameWithoutExt = $fileNameWithoutExts[0];

        // 默认本地上传
        $uploadPath = 'uploads/files/'.$fileName;
        $getResult = Storage::disk('public')->put($uploadPath,$audioContent);

        if($getResult) {

            $path = 'public/'.$uploadPath;

            // 数据
            $realFilePath = storage_path('app/').$path;

            // '2>$1' 配置管道输出错误，方便调试
            $command = '/usr/local/ffmpeg/bin/ffmpeg -i '.$realFilePath.' -acodec pcm_s16le -ac 1 -ar 8000 '.storage_path('app/').'public/uploads/files/'.$fileNameWithoutExt.'.wav 2>&1';

            $status = shell_exec($command);
        }

        // 语音数据地址
        $URI = storage_path('app/').'public/uploads/files/'.$fileNameWithoutExt.'.wav';

        if (empty($secretKey)) {
            return self::error("secretKey不能为空！");
        }

        if (empty($SecretId)) {
            return self::error("SecretId不能为空！");
        }

        if (empty($URI)) {
            return self::error("URI不能为空！");
        }

        $params = array();
        $params['Action'] = 'SentenceRecognition';
        $params['SecretId'] = $SecretId;
        $params['Timestamp'] = time();
        $params['Nonce'] = substr($params['Timestamp'], 0, 4);
        $params['Version'] = '2018-05-22';
        $params['ProjectId'] = 0;
        $params['SubServiceType'] = 2;
        $params['EngSerViceType'] = '8k';
        $params['SourceType'] = $SourceType;

        if ($params['SourceType'] == 0) {
            $voice = $URI;
            $voice = urlencode($voice);
            $params['Url'] = $voice;
        } else if ($params['SourceType'] == 1) {
            $file_path = $URI;
            if (file_exists($file_path)) {
                $handle = fopen($file_path, "rb");
                $str = fread($handle, filesize($file_path));
                fclose($handle);
                $strlen = strlen($str);
                $str = base64_encode($str);
                $params["Data"] = $str;
                $params["DataLen"] = $strlen;
            } else {
                return self::error("文件不存在！");
            }
        }
        $params['VoiceFormat'] = 'wav';
        $params['UsrAudioKey'] =  substr(str_shuffle("QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm"), 26, 16);
    
        $tmpParam = array();

        ksort($params);

        foreach ($params as $key => $value) {
            array_push($tmpParam, str_replace("_", ".", $key) . "=" . $value);
        }

        $strParam = join("&", $tmpParam);
        $signStr = strtoupper('POST') . 'aai.tencentcloudapi.com/?' . $strParam;
    
        $sign = base64_encode(hash_hmac('sha1', $signStr, $secretKey, true));
    
        $params['Signature'] = $sign;
    
        $url = 'https://aai.tencentcloudapi.com';
        $headers = array("Host:aai.tencentcloudapi.com", "Content-Type:application/x-www-form-urlencoded", "charset=UTF-8");

        $result = curl($url, $params, $method = 'post', $headers);

        return json_decode($result,true);
    }
}
