<?php

/*阿里云-内容安全-图片审核-同步检测

    https://help.aliyun.com/document_detail/53415.html?spm=a2c4g.11186623.2.14.576c540179VAK1#reference-rxv-cw4-w2b

*/

$AccessKeyId='';
$AccessKey_Secret='';

$api='/green/text/scan';
$url='https://green.cn-shenzhen.aliyuncs.com'.$api;


// $clientInfo='{"userId":"120234234","userNick":"Mike","userType":"others"}';//选填

//请求内容json
$dataid = uniqid();
$content = '你大爷的';
$body='{"scenes":["antispam"],"tasks":[{"dataId":"'.$dataid.'","content":"'.$content.'"}]}';


//配置header头
$arr=[
    'Accept'=>'application/json',
    'Content-MD5'=>base64_encode(md5($body,1)),
    'Content-Type'=>'application/json',
    'Date'=>gmdate('D, d M Y H:i:s T',time()),//'Tue, 17 Jan 2017 10:16:36 GMT',注意时区
];

$arr1=[
    'x-acs-version'=>'2018-05-09',
    'x-acs-signature-nonce'=>uniqid(),
    'x-acs-signature-version'=>'1.0',
    'x-acs-signature-method'=>'HMAC-SHA1',
];
ksort($arr1);

$arr2=array_merge($arr,$arr1);

$str="POST\n";
foreach ($arr2 as $k => $v) {
    if(strpos($k,'x-acs')===0){
        $str.=$k.':'.$v."\n";
    }else{
        $str.=$v."\n";
    }
}
if(isset($clientInfo)){
    $str.="{$api}?clientInfo=".$clientInfo;
}else{
    $str.=$api;
}

$signature = base64_encode(hash_hmac("sha1", $str, $AccessKey_Secret, true));
// echo '<pre>';print_r($signature);die;

$Authorization=['Authorization'=>"acs {$AccessKeyId}:{$signature}"];

//得到完整header头
$http_header=array_merge($arr2,$Authorization);
$header_arr=[];
foreach ($http_header as $k => $v) {
    $header_arr[]=$k.':'.$v;
}
// echo '<pre>';print_r($header_arr);die;

if(isset($clientInfo)){
    $url=$url.'?clientInfo='.urlencode($clientInfo);
}

//执行请求
$res=excurl($url,$ispost='1',$body,$header_arr);

echo '<pre>';print_r(json_decode($res,1));



/**curl请求
 * $header=array(
'Content-Type:'.'application/json',
// 'X-Debug-Mode:'.'1'
);
 */
function excurl($url,$ispost='',$arr='',$header=''){

    $ch = curl_init();
    if(stripos($url,"https://")!==FALSE){
        //关闭证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_HEADER, 0);//返回response头部信息
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
    //post方式
    if(!empty($ispost)){
        if(is_array($arr)){
            $content=http_build_query($arr);//入参内容
        }else{
            $content=$arr;
        }
        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$content);//所传参
    }
    $sContent = curl_exec($ch);
    $aStatus = curl_getinfo($ch);
    curl_close($ch);
    //返回
    return $sContent;
}