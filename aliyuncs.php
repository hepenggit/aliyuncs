<?php
require_once 'aliyuncs/aliyun-oss-php-sdk/autoload.php';
include_once 'aliyuncs/aliyun-php-sdk-core/Config.php';

use Green\Request\V20180509 as Green;
use Green\Request\Extension\ClientUploader;
class TextScan{

	private static $accessKeyId='';

    private static $accessKeySecret='';

	static public function check_text($content){

        $iClientProfile = \DefaultProfile::getProfile("cn-shanghai", self::$accessKeyId, self::$accessKeySecret);

        \DefaultProfile::addEndpoint("cn-shanghai", "cn-shanghai", "Green", "green.cn-shanghai.aliyuncs.com");

        $client = new \DefaultAcsClient($iClientProfile);

        $request = new Green\TextScanRequest();

        $request->setMethod("POST");

        $request->setAcceptFormat("JSON");

        $task1 = array('dataId' => uniqid(),

        'content' => $content

        );

        /**

        * 文本垃圾检测： antispam

        * 关键词检测： keyword

        **/

        $request->setContent(json_encode(array("tasks" => array($task1),

        "scenes" => array("antispam"))));

        try {

            $response = $client->getAcsResponse($request);
            // print_r($response);
            $result=array();

            if(200 == $response->code){

                $taskResults = $response->data;

                foreach ($taskResults as $taskResult) {

                    if(200 == $taskResult->code){

                        $sceneResults = $taskResult->results;
                        var_dump($sceneResults);
                        foreach ($sceneResults as $sceneResult) {

                            $scene = $sceneResult->scene;

                            $suggestion = $sceneResult->suggestion;

                            $result=$taskResult;
                        }
                    }else{

                        print_r("task process fail:" + $response->code);

                    }
                }

            }else{
                print_r("detect not success. code:" + $response->code);
            }

            $result=$result->results[0];
            $data=array();

            if($result->label == 'normal'){
                $data['code']=true;
                $data['label']=$result->label;
            }else{
                $data['code']=false;
                $data['label']=self::getlabel($result->label);
            }
            return $data;
        } catch (Exception $e) {
            print_r($e);
        }
    }

    static private function getlabel($label){

        switch ($label){

            case  'normal':

                return '正常文本';

                break;

            case  'spam':

                return '输入的内容含垃圾信息';

                break;

            case  'ad':

                return '输入的内容含广告';

                break;

            case  'politics':

                return '输入的内容含渉政';

                break;

            case  'terrorism':

                return '输入的内容含暴恐';

                break;

            case  'abuse':

                return '输入的内容含辱骂';

                break;

            case  'porn':

                return '输入的内容含色情';

                break;

            case  'flood':

                return '输入的内容含灌水';

                break;

            case  'contraband':

                return '输入的内容含垃违禁';

                break;

            case  'customized':

                return '输入的内容包含敏感词';

                break;

            default:

                return '';

                break;

        }
    }


    static public function check_img($img_url = array()){
    	// 请替换成您的AccessKey信息。
		$iClientProfile = DefaultProfile::getProfile("cn-shanghai", self::$accessKeyId, self::$accessKeySecret);
		DefaultProfile::addEndpoint("cn-shanghai", "cn-shanghai", "Green", "green.cn-shanghai.aliyuncs.com");
		$client = new DefaultAcsClient($iClientProfile);

		$request = new Green\ImageSyncScanRequest();
		$request->setMethod("POST");
		$request->setAcceptFormat("JSON");
		$task1 = array('dataId' =>  uniqid(),
		    'url' => 'https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fimage.wangchao.net.cn%2Ffengjing%2F1324809443221.jpg'
		);

		// 设置待检测的图片，一张图片对应一个检测任务。
		// 多张图片同时检测时，处理时间由最后一张处理完的图片决定。
		// 通常情况下批量检测的平均响应时间比单张检测要长。一次批量提交的图片数越多，响应时间被拉长的概率越高。
		// 代码中以单张图片检测作为示例，如果需要批量检测多张图片，请自行构建多个检测任务。
		// 一次请求中可以同时检测多张图片，每张图片可以同时检测多个风险场景，计费按照单图片单场景检测叠加计算。
		// 例如，检测2张图片，场景传递porn和terrorism，则计费按照2张图片鉴黄和2张图片暴恐检测计算。
		$request->setContent(json_encode(array("tasks" => array($task1),
		    "scenes" => array("porn","terrorism"))));
		try {
		    $response = $client->getAcsResponse($request);
		    // print_r($response);
		    $result = array();
		    if(200 == $response->code){
		        $taskResults = $response->data;
		        foreach ($taskResults as $taskResult) {
		            if(200 == $taskResult->code){
		                $sceneResults = $taskResult->results;
		                foreach ($sceneResults as $sceneResult) {
		                    $scene = $sceneResult->scene;
		                    $suggestion = $sceneResult->suggestion;
		                    // 根据scene和suggetion设置后续操作。
		                    // 根据不同的suggestion结果做业务上的不同处理。例如，将违规数据删除等。
		                    $result=$taskResult;
		                }
		            }else{
		                print_r("task process fail:" + $response->code);
		            }
		        }
		    }else{
		        print_r("detect not success. code:" + $response->code);
		    }
		} catch (Exception $e) {
		    print_r($e);
		}

	    $result=$result->results[1];
        $data=array();

        if($result->label == 'pass'){
            $data['code']=true;
            $data['label']=$this->getimglabel($result->label,$result->scene);
        }else{
            $data['code']=false;
            $data['label']=$this->getimglabel($result->label,$result->scene);
        }
        return $data;
    }

	//图片检测反馈描述
    public function getimglabel($label,$scene){
        if($scene=='porn'){
            if($label=='normal'){
               return '正常图片，无色情内容';
            }elseif($label=='sexy'){
                return '性感图片';
            }elseif($label=='porn'){
                return '色情图片';
            }
        }elseif($scene=='terrorism'){
            if($label=='normal'){
                return '正常图片';
            }elseif($label=='bloody'){
                return '血腥';
            }elseif($label=='explosion'){
                return '爆炸烟光';
            }elseif($label=='outfit'){
                return '特殊装束';
            }elseif($label=='logo'){
                return '特殊标识';
            }elseif($label=='weapon'){
                return '武器';
            }elseif($label=='politics'){
                return '涉政';
            }elseif($label=='violence'){
                return '打斗';
            }elseif($label=='crowd'){
                return '聚众';
            }elseif($label=='parade'){
                return '游行';
            }elseif($label=='carcrash'){
                return '车祸现场';
            }elseif($label=='flag'){
                return '旗帜';
            }elseif($label=='location'){
                return '地标';
            }elseif($label=='others'){
                return '其他';
            }
        }elseif($scene=='ad'){
            if($label=='normal'){
               return '正常图片';
            }elseif($label=='ad'){
               return '其他广告';
            }
        }elseif($scene=='qrcode'){
            if($label=='normal'){
               return '正常图片';
            }elseif($label=='qrcode'){
               return '含二维码的图片';
            }
        }elseif($scene=='live'){
            if($label=='normal'){
               return '正常图片';
            }elseif($label=='meaningless'){
               return '无意义图片';
            }elseif($label=='PIP'){
               return '画中画';
            }elseif($label=='smoking'){
               return '吸烟';
            }elseif($label=='drivelive'){
               return '车内直播';
            }
        }elseif($scene=='qrcode'){
            if($label=='normal'){
               return '正常图片';
            }elseif($label=='TV'){
               return '带有管控logo的图片';
            }elseif($label=='trademark'){
               return '商标';
            }
        }

    }

}

$TextScan1 = new TextScan();
echo '<pre>';
$res = $TextScan1::check_text('咚咚咚');
var_dump($res);
$imgs = ['https://gimg2.baidu.com/image_search/src=http%3A%2F%2Fimage.wangchao.net.cn%2Ffengjing%2F1324809443221.jpg'];
$res_img = $TextScan1::check_img($imgs);
var_dump($res_img);