<?php
/**
 * Created by PhpStorm.
 * User: wenquan
 * Date: 14-7-9
 * Time: 上午10:33
 * userage:
 */
require './lib/medoo.php';

$db = new medoo([
    // required
    'database_type' => 'mysql',
    'database_name' => 'spider',
    'server' => 'localhost',
    'username' => 'root',
    'password' => 'icanfly1983',

    // optional
    'port' => 3306,
    'charset' => 'utf8',
    // driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
    'option' => [
        PDO::ATTR_CASE => PDO::CASE_NATURAL
    ]
]);

//$db->select("onethink_hosts","*",["stat"=>1]);
$res = $db->query("select * from onethink_spider_hosts where stat = '1' order by level desc")->fetchAll();
foreach($res as $key =>$val){
    $db->query("update `onethink_spider_hosts` set `stat`='2' where `id` = ".$val['id'].";");
    //$db->insert("onethink_spider_logs",[]);
    spider($val);

}


function spider($arr){
    global $db;
    //$res = model_http_curl_get();


}

/*
 * 单线程
 */
function model_http_curl_get($url,$userAgent="",$cookie="")
{
    if(is_array($userAgent)){
        $k = rand(0,count($userAgent));
        $ua = $userAgent[$k];
    }else{
        $ua = $userAgent ? $userAgent : 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.2)';
    }
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_TIMEOUT, 5);
    curl_setopt($curl, CURLOPT_USERAGENT, $ua);
    if($cookie!=""){
        //curl_setopt($curl,CURLOPT_COOKIE,$cookie);
    }
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

function get_first_page(){
    global $db;
    $res = $db->query("select * from onethink_spider_hosts where stat = '1' order by level desc")->fetchAll();
    foreach($res as $key =>$val){
        $st= time();
        $str = model_http_curl_get($val['url']);
        $usetime = time()-$st;
        $ut[$val['url']]=$usetime;
        file_put_contents("./data/".$res.".html",$str);
        echo $val['url']."\n";
    }
    file_put_contents("./data/".date("Ymd",time())."-time.txt",serialize($ut));
    return $ut;
}


get_first_page();