<?php
if (isset($_POST['sub'])){
$number= $_POST['number']; // Принимаем массив данных с всех checkbox и заносим в переменную

if (is_array($number)) { // Проверяем, является ли эта переменная массивом данных, если да, то исполняем код в цикле for, если нет, выходим из выполнения скрипта
foreach ($number as $key => $value) {
        $del = "/var/www/stat/box/";
        $del .= $value;
		//echo $del."<br>";
        unlink($del);
}
} else exit('Это не массив');
}
?>

<html>
 <head>
  <meta charset="utf-8">
  <link href="css2/style.css" rel="stylesheet">
  <link href="css2/stickyRows.css" rel="stylesheet">
 </head>
<body>
	<table class="table-with-sticky-rows">
	<form action="f.php" method="post">
<thead>
	<tr>
		<td>ID</td>
		<td>Owner</td>
		<td>Address</td>
		<td>VPN key</td>
		<td>Last ping</td>
		<td>Uptime, min</td>
		<td>Temp, C</td>
		<td>Files, %</td>
		<td>Disk, %</td>
		<td>Writable</td>
		<td>Image</td>
		<td>GPS</td>
		<td>GPS queue</td>
		<td>SEND queue</td>
		<td>Accuracy, %</td>
		<td>Last stop</td>
		<td>Version</td>
		<td>Map</td>
		<td>reboots</td>
		<td>traffic MB</td>
		<td><input  type="submit" name="sub" value="delete"></td>
	</tr>
</thead>
<tbody>
<?php
$owner_json = json_decode(file_get_contents("/var/www/stat/box/owners/test/owner.json"), true);
$files = array();
//print_r($owner_json);
foreach (glob("data/*") as $filename) {
	$afile = split('\.',$filename);
	$tmp_owner = "";
	if ($afile[1]=="back") {
                $data = parse_ini_file($afile[0].".front");
					foreach ($owner_json as $owner => $cars){
						foreach ($cars as $car){
							foreach($car as $key => $value){
								if (is_array($value)){
								foreach($value as $numberplate){
									//print_r($key);
									//echo "<br>";
									if ($key == "car"){
										if ($numberplate == $data['id']){
											$tmp_owner = $owner;
											$tmp_city = $car['city'];
										}
									}
								}
								}
							}
						}
					}
				if ($tmp_owner === "") 
					$tmp_owner = "test";
                $files[$filename]=$tmp_city." ".$tmp_owner." ".$data['key']." ";
	}
	else {
		$data = parse_ini_file($filename);
			foreach ($owner_json as $owner => $cars){
				foreach ($cars as $car){
					foreach($car as $key => $value){
						if (is_array($value)){
						foreach($value as $numberplate){
						//print_r($key);
						//echo "<br>";
							if ($key == "car"){
								//echo $numberplate;
								if ($numberplate == $data['id']){
									//echo $value;
									$tmp_owner = $owner;
									$tmp_city = $car['city'];
								}
							}
						}
						}
					}
				}
			}
		if ($tmp_owner === "") 
			$tmp_owner = "test";
		$files[$filename]=$tmp_city." ".$tmp_owner." ".$data['key'];
	}
}
ini_set('memory_limit', '512M' );
array_multisort($files,SORT_ASC,SORT_NATURAL);
        $str = (string) date("d.m.Y");
		 // $str = "26.05.2015";
        $file = "/var/www/stat/box/cord-uptime/".$str.".json";
		 // var_dump($file);
        $json = json_decode(file_get_contents($file),true);

		  //$json = json_decode($json, true);
		   //var_dump($json);
        $i=0;
        $arr = array();

		$old_owner_row_name = "";
		
foreach ($files as $filename=>$value) {
	$afile = split('\.',$filename);
        $data = parse_ini_file($filename);
	if ($data['host'] == 'front'){
		$bbb = $afile[0]."-first";
		$door = explode('/',$bbb);
	} else{
		$bbb = $afile[0]."-second";
		$door = explode('/',$bbb);
	}
	$without_owner = true;
	foreach ($owner_json as $owner => $cars){
		foreach ($cars as $car){
			foreach($car as $key => $value){
				if (is_array($value)){
				foreach($value as $numberplate){
				//print_r($car['desc']);
				//echo "<br>";
					if ($key == "car"){
						//echo $numberplate;
						if ($numberplate == $data['id']){
							//echo $value;
							$owner_row_name = $owner;
							$owner_row_desc = $car['desc'];
							$owner_row_city = $car['city'];
							$without_owner = false;
						}
					}
					
				}
				}
			}
		}
	}
	$owner_row_class = "";
if ($owner_row_name != $old_owner_row_name){
?>
<tr>
	<td class="header" colspan="21">
		<div class="container">
			<div class="box title">
				<?= $owner_row_name ?>
			</div>
			<div class="box city">
				<?= $owner_row_city ?>
			</div>
			<div class="box desc">
				<?= $owner_row_desc ?>
			</div>
		</div>
	<td>
</tr>

<?php
	}
	$old_owner_row_name = $owner_row_name;
        if (strpos($data['version'],"live")==true) {
		print "<tr class=\"live\">";
        }
        else {
		print "<tr class=\"".(($data['host']=='back')?"back":"front")."\">";
        }
	//print "<td><a style='color: black' href='http://192.168.0.196/cacti/graph_view.php?action=preview&host_id=0&rows=-1&graph_template_id=0&filter=".(($data['host']=='back')?$data['id']."-second":$data['id']."-first")."' target=\"_blank\" title=\"график uptime'ов\">".$data['id']."</a></td>";
	print "<td>".$data['id']."</td>";
	
	/*foreach ($owner_json as $owner => $cars){
			foreach ($cars as $car){
				if ($car == $data['id']){
					print "<td>".$owner."</td>";
					$without_owner = false;
				}
			}
	}*/
	if ($without_owner == false){
		print "<td>".$owner_row_name."</td>";
	} else if ($without_owner == true){
		print "<td></td>";
	}
	//print "<td>".file_get_contents("owners/".$data['id'])."</td>";
	if ($data['host']=='back' && !(isset($data['key']))) {
		$dataf = parse_ini_file($afile[0].".front");
		print "<td><a target='_blank' href='http://".$dataf['address'].":88'>---></td>";
	}
	else {
		print "<td><a target='_blank' href='http://".$data['address']."'>".$data['address']."</a></td>";
	}
		$car_key = (isset($data['key']))? $data['key']:"";
        print "<td>".$car_key."</td>";

	$ts = (int)((time()-$data['stamp'])/60);

	print "<td class=\"".(($ts>5)?"alert\">$ts":"ok\">ok")."</td>";
        print "<td class=\"".(($data['uptime']<60)?"alert":"ok")."\">".$data['uptime']."</td>";
        print "<td class=\"".(($data['temp']>=60)?"alert":"ok")."\">".$data['temp']."</td>";
	if (isset($data['nodes'])){
		print "<td class=\"".(($data['nodes']>=30)?"alert":"ok")."\">".$data['nodes']."</td>";
    } else print '<td></td>';
		print "<td class=\"".(($data['size']>=30)?"alert":"ok")."\">".$data['size']."</td>";
	if (isset($data['writable'])){
		print "<td class=\"".(($data['writable']!='OK')?"alert":"ok")."\">".$data['writable']."</td>"; 
	} else print '<td></td>';
	print "<td class=\"".(($data['image']<1)?"alert":"ok")."\">".$data['image']."</td>";
	if (isset($data['gps'])){
        print "<td class=\"".(($data['gps']>=60)?"alert\">fail":"ok\">ok")."</td>";
	} else print "<td class=\"ok\">ok</td>";
	if (isset($data['gpssize'])){
		print "<td class=\"".(($data['gpssize']>=1000)?"alert":"ok")."\">".$data['gpssize']."</td>";
	} else print "<td></td>";
		print "<td class=\"".(($data['tosend']>=10)?"alert":"ok")."\">".$data['tosend']."</td>";
	list($in,$out)=split(',',$data['flow']);
	if (strpos($in,'null') || $in=='' || $out=='') {
		$acc = "0";
	}
	else {
		if (max($in,$out) != 0)
		$acc = 100-abs((int)(($in-$out)*100/max($in,$out)));
	}
	//print "<td class=\"".(($acc<80)?"alert":"ok")."\"><a target='_blank' href=http://pcounter.gemicle.com/reports/online/transportId/".$data['id'].">".$acc."</a></td>";
	print "<td class=\"".(($acc<80)?"alert":"ok")."\"><a target='_blank' href=http://5.9.82.226/reports/online/transportId/".$data['id'].">".$acc."</a></td>";
        //print "<td class=\"".(($data['sended']>2500)?"alert":"ok")."\">".(int)($data['sended']/60)."</td>";
	if (isset($data['count'])){
	print "<td class=\"".(($data['count']>40)?"alert":"ok")."\">".(int)($data['count'])."</td>";
	} else print '<td></td>';
	print "<td>".$data['version']."</td>";
	print "<td><a target='_blank' href='http://maps.google.com/maps?q=description+(".$data['host'].")+%40".$data['coordinates']."'>".(($data['coordinates']=='')?"":"map")."</a></td>";
	
	$arr = array();
	$check = false;
	foreach ($json as $k1 => $v1){
		//echo $door[1]." ";
		$lol = $door[1];
		$lol = preg_replace('/[^a-zA-Z0-9-_]/', '', $lol);
                if ($k1 == $lol){
			//echo $k1." ";
                        $j=0;
						//$arr[$k1] = array();
                        array_push($arr[$k1][],0);
                        foreach ($v1 as $k2 => $v2){
                                //$result = count($v1)-1;
                                foreach ($v2 as $k3 => $v3){
                                        if ($j != 0){
                                                if ($temp > $v2['uptime']){
                                                        array_push($arr[$k1], array("time" => $l_time, "uptime" => $v2['uptime'], "cord" => $l_cord, "host" => $v2['host']));	
												}
                                        }
										$l_cord = $v2['cord'];
                                        $temp = $v2['uptime'];
					$l_time = $v2['time'];
                                }
                                $j++;
                        }
$myclass = "ok";
		foreach ($arr as $k4 => $v4){
			$down_count = count($v4);
			$down_count--;
			if ($down_count > 3){ 
				$myclass = "alert";
			}
			$flag = 0;
			$count_reboot = count($v4)-21;
			if ($count_reboot < 0){
				$lolclass = "liblock";
			}
			else {
				$lolclass = "linone";
			}
			//echo $count_reboot."<br>";
			print "<td class=\"".$myclass."\" style=\"width:110px; text-align: center;\" ><ol class=\"someShit\">";
			foreach ($v4 as $k5 => $v5){
				if ($count_reboot >= 0){
					$count_reboot--;
				} 
				if ($count_reboot < 0) {
					
					print "<li class=\"".$lolclass."\"><a target='_blank' style='text-decoration: none; color: black' href='http://maps.google.com/maps?q=description+(".$v5['host'].")+%40".$v5['cord']."'>".$v5['time']."</a></li>";
					
				}
			} 
			print "</ol><strong><div class=\"myShower fa fa-angle-down\">".$down_count."</div></strong></td>";
		}
		$check = true;
                }
                $i++;
				
    }
	if ($check == false){
		print "<td class=\"".$myclass."\" style=\"width:110px; text-align: center;\" ><ol class=\"someShit\">";
		print "</ol><strong><div class=\"myShower fa fa-angle-down\">-</div></strong></td>";
	}
	if (isset($data['trafic'])){
		print '<td class="'.((round($data['trafic']/1024/1024, 2) > 10)?"alert":"ok").'">'.(($data['host'] == 'back')?"":round($data['trafic']/1024/1024, 2)).'</td>';
	} else print '<td></td>';
//print "<td class=\"".(($data['registrator']==1)?"alert":"ok")."\">".$data['registrator']."</td>";
print "<td><input type=\"checkbox\" name=\"number[]\" value=\"".$filename."\"></td>";
print "</tr>";
}
print "</tbody></table>";
?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
<script src="script.js"></script>
<script src="stickyRows.js"></script>
<script>
$(function(){
  $('.table-with-sticky-rows').stickyRows();
});
</script>
</body></html>

