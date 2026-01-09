<?php
@header('P3P: CP="CAO PSA OUR"');
set_time_limit(0);
$cityname=$_GET[city];

//缓存
set_time_limit(0);
//echo date('d').date('m').date('Y');
function isvalid($filename) {      //确认缓存文件
	if (!file_exists("./cache/{$filename}.htm")) return false;  
	if (!(@$mtime = filemtime("./cache/{$filename}.htm"))) return false;
	$ctime=mktime()-$mtime; //时差 
	//设置缓存时间 
	if ($ctime>1800) return false; 
	//echo $mtime."</br>"; 
	//echo mktime()."</br>";
	//echo $ctime;
	return true;  
}  
//转utf为gbk
function togbk($str){
	if($str!=''){
		$gbk_str=mb_convert_encoding($str, "GBK", "UTF-8"); 
	}else{
		$str='';
	}
	return $gbk_str;

}
//set timeout
$zhsc_opts = array('http'=>array('method'=>"GET",'timeout'=>1,'header'=>"Host: zh.wikipedia.org\r\n" . 
              "Accept-language: zh-cn\r\n" . 
              "User-Agent: Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; 4399Box.560; .NET4.0C; .NET4.0E)" .
              "Accept: *//*"));
$zhsc_context = stream_context_create($zhsc_opts);

if(is_numeric($cityname))
{ 

	if(!isvalid($cityname))
	{ 
		$url = "http://m.weather.com.cn/data/{$cityname}.html";
		$fcontents = @file_get_contents($url,false,$zhsc_context);
		if($fcontents){
			if(file_exists("./cache/{$cityname}.htm")){unlink("./cache/{$cityname}.htm");}
			$handle = fopen("./cache/{$cityname}.htm", "w");
			fwrite($handle, $fcontents);
			fclose($handle);
		}else{
			if(file_exists("./cache/{$cityname}.htm")){
				$fcontents = file_get_contents("./cache/{$cityname}.htm");
			}else{
				$fcontents = '';
			}
		}
	}else{
		$fcontents = file_get_contents("./cache/{$cityname}.htm");	
	}


		if($fcontents!=''){
			$weather_zs=(array)json_decode($fcontents)->weatherinfo;
			
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<meta http-equiv="x-ua-compatible" content="ie=7" />
<title><?php echo togbk($weather_zs['city']); ?>天气预报_<?php echo togbk($weather_zs['city']); ?>地区今天和明天2天天气预报_要哇导航网</title>
<meta name="description" content="" />
<style type="text/css">
*{ margin:0; padding:0;}
li{ list-style:none;}
.weather{ width:99%; height:78px; overflow:hidden;margin-top:0px;}
.weather ul{ margin:3px 0 0 0px; }
.weather ul li{ float:left;height:72px; padding-top:6px;overflow:hidden; font-size:12px; color:#fff; line-height:20px;}
.tb_int3{ height:20px;}
.weather ul .today_pic{ width:13%; height:53px;overflow:hidden; margin-top:8px; background:url(i/<?php echo togbk($weather_zs['img1']); ?>.png?<?php echo time(); ?>) no-repeat;overflow:hidden;
_background:none;
_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod="crop",src="i/<?php echo togbk($weather_zs['img1']); ?>.png?<?php echo time(); ?>");}
.weather ul .today{ width:20%;}
.weather ul b,.weather ul span{ font-size:14px;}
.weather ul span a{ text-decoration:none; color:#fff;}
.weather ul span a:hover{ text-decoration:underline;}
.weather ul .tomorrow_pic{ width:13%; height:53px; margin-top:8px; background:url(i/<?php echo togbk($weather_zs['img3']); ?>.png?<?php echo time(); ?>) no-repeat;overflow:hidden;
_background:none;
_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod="crop",src="i/<?php echo togbk($weather_zs['img3']); ?>.png?<?php echo time(); ?>");}
.weather ul .tomorrow{ width:20%;}
.weather ul .aftertomorrow_pic{ width:13%; height:53px; margin-top:8px; background:url(i/<?php echo togbk($weather_zs['img5']); ?>.png?<?php echo time(); ?>) no-repeat;overflow:hidden;
_background:none;
_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod="crop",src="i/<?php echo togbk($weather_zs['img5']); ?>.png?<?php echo time(); ?>");}
.weather ul .aftertomorrow{ width:20%;}
.wdth{ width:81px; overflow:hidden; height:20px;}
.butt{ width:100px;}
.butt_chaxun{ width:60px;}
.dz2{ color:#fff; font-size:13px; padding-left:10px; margin-top:10px;}
.dz2 a{ text-decoration:underline; color:#fff;}
</style>
</head>  
<body style="background-color:transparent">
<div id="dz1">
<div class="weather fl"><ul><li class="today_pic"></li><li class="today"><p><b style="width:30px; overflow:hidden; display:block; float:left;"><nobr><?php echo togbk($weather_zs['city']); ?></nobr></b><span>[<a href="javascript:dz(0)" target="_self">定制</a>]</span></p><p class="wdth"><?php echo togbk($weather_zs['weather1']); ?></p><p><?php echo togbk($weather_zs['temp1']); ?></p></li><li class="tomorrow_pic"></li><li class="tomorrow"><p><b>明天</b></p><p class="wdth"><?php echo togbk($weather_zs['weather2']); ?></p><p><?php echo togbk($weather_zs['temp2']); ?></p></li>
<li class="aftertomorrow_pic"></li><li class="aftertomorrow"><p><b>后天</b></p><p class="wdth"><?php echo togbk($weather_zs['weather3']); ?></p><p><?php echo togbk($weather_zs['temp3']); ?></p></li>
<p class="clear"></p></ul></div>
</div>
<div id="dz2" class="dz2" style="display:none">
<table width="100%" border="0" cellspacing="0" cellpadding="2"><form id="custom" name="custom" method="post" action="">
<tr><td width="66" height="32">省份选择:</td>
<td><label>
<select id="province" name="province" onChange="change_city(this.value)" class="tb_int3" style="width:130px">					 
</select>
</label></td>	 
<td>
<input type="button" name="Submit" value="自动判断" class="butt" onclick="clearset()" />
</td>
<td>&lt;&lt; <a href="javascript:dz(1)" class="cW"><u>返回</u></a></td>				 
</tr>
<tr>
<td>城市选择:</td>
<td>
<select name="city" id="city"  class="tb_int3" style="width:130px">
</select>
</td><td><input type="button" name="Submit" value="定制城市" class="butt" onclick="dzset()" /></td>
<td><input type="button" name="Submit" value="查询" class="butt_chaxun" onclick="dzshow()" /></td>
</tr></form>
</table>
</div>
<script language="javascript"> 
var js;
var istop=top.location==self.location
function $(a){return document.getElementById(a);}
function dz(a)
{
if(a==0){
var script=document.createElement("script") 
script.src   =  "/js/wz_tianqi.js?r="+Math.random();
 document.body.appendChild(script); 
}
b="",c="none";
if(a==1) b="none",c="";
$("dz1").style.display=c;
$("dz2").style.display=b;
}
</script>
</body>
</html>
<?php			
			//print_r($weather_zs);exit();
		}else{
			echo "获取天气超时！";
		}
		
		//echo $fcontents;exit();
}
?>