<?php
header('Content-type:text/json;charset=utf-8');
include_once("data/config.php");
include_once("inc/function.php");
loginchk($userid);
$gotourl = "";
$success = "0";
$getaction = get("action");
if($getaction=="getclassify"){	
	header('Content-type:text/html;charset=utf-8');
	$sql = "select * from ".TABLE."account_class where ufid='$userid' and classtype='$_GET[classtype]' and classid<>'$_GET[classid]'";
    $query = mysqli_query($conn,$sql);
    while ($row = mysqli_fetch_array($query)){
		echo "<option value='".$row["classid"]."'>".$row["classname"]."</option>";
	}
}
if($getaction=="addrecord"){
	$classid = post("classid");
	$money = post("money");
	$zhifu = post("zhifu");
	$remark = post("remark");
	$addtime = strtotime(post("time"));
	if(empty($classid) or empty($money) or empty($zhifu)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($money)){
		$error_code = "金额非法！";
	}else{
		$sql = "insert into ".TABLE."account (acmoney, acclassid, actime, acremark, jiid, zhifu) values ('$money', '$classid', '$addtime', '$remark', '$userid', '$zhifu')";
		$query = mysqli_query($conn,$sql);
		if($query){
			$success = "1";
			$error_code = "保存成功！";
			if($zhifu=="1"){
				$gotourl = "add.php?action=income";
			}else{
				$gotourl = "add.php?action=pay";
			}			
		}else{
			$error_code = "保存失败！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="saverecord"){
	$id = post("edit-id");
	$money = post("edit-money");
	$remark = post("edit-remark");	
	$addtime = strtotime(post("edit-time"));
	if(empty($id) or empty($money)){
		$error_code = "缺少参数！";
	}elseif(!is_numeric($money)){
		$error_code = "金额非法！";
	}else{
		$sql = "update ".TABLE."account set acmoney='".$money."',acremark='".$remark."',actime='".$addtime."' where acid='".$id."' and jiid='".$userid."'";
		$result = mysqli_query($conn,$sql);
		if ($result) {
			$success = "1";
			$error_code = "保存成功！";
		} else {
			$error_code = "保存失败！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deleterecord"){
	header('Content-type:text/html;charset=utf-8');
	$get_id = get("id");
	if(empty($get_id) || !is_numeric($get_id)){
		$error_code = "缺少参数或参数非法！";
	}else{
		$sql = "delete from ".TABLE."account where acid='$get_id' and jiid='$userid'";
		if(mysqli_query($conn,$sql)){
			$error_code = "删除成功！";
		}else{
			$error_code = "删除失败！";
		}
	}				
	echo $error_code;
}
if($getaction=="deleterecordAll"){
	if(isset($_POST["del_id"]) && $_POST["del_id"] != ""){
		$del_id = implode(",",$_POST['del_id']);
		$sql = "delete from ".TABLE."account where jiid='$userid' and acid in ($del_id)";
		if (mysqli_query($conn,$sql)){
			$success = "1";
			$error_code = "删除成功！";
		}else{
			$error_code = "删除失败！";
		}	
	}else{
		$error_code = "参数不正确！";
	}			
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="changeclassify"){
	$classid = post("classid");
	$newclassid = post("newclassid");
	if(empty($newclassid) or $newclassid=="0" or empty($classid)){
		$error_code = "缺少参数或者未选择目标分类！";
	}else{
		$sql = "update ".TABLE."account set acclassid='$newclassid' where acclassid='$classid' and jiid='$userid'";
		$query = mysqli_query($conn,$sql);
		if ($query) {
			$success = "1";
			$error_code = "更改分类成功！";
			$gotourl = "classify.php";
		} else {
			$error_code = "保存失败！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="deleteclassify"){
	header('Content-type:text/html;charset=utf-8');
	$sql = "select acid from ".TABLE."account where acclassid='$_GET[classid]' and jiid='$userid'";
	$query = mysqli_query($conn,$sql);
	if ($row = mysqli_fetch_array($query)) {
		$error_code = "在此分类下有账目，请将账目转移到其他分类";
	} else {
		$sql = "delete from ".TABLE."account_class where classid=".$_GET['classid'];
		if (mysqli_query($conn,$sql)){
			$error_code = "分类删除成功！";
		}else{
			$error_code = "删除失败！";
		}			
	}
	echo $error_code;
}
if($getaction=="addclassify"){
	$classname = post("classname");
	$classtype = post("classtype");
	if(empty($classname)){
		$error_code = "分类名称不能为空！";
	}elseif(strlen($classname)>18){
		$error_code = "分类名称不能大于6个字！";
	}else{
		$sql = "select * from ".TABLE."account_class where classname='$classname' and classtype='$classtype' and ufid='$userid'";
		$query = mysqli_query($conn,$sql);
		$attitle = is_array($row = mysqli_fetch_array($query));
		if ($attitle) {
			$error_code = "该名称的分类已经存在！";
		}
		else {
			$sql = "insert into ".TABLE."account_class (classname, classtype, ufid) values ('$classname', '$classtype',$userid)";
			$query = mysqli_query($conn,$sql);
			if ($query) {
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "classify.php";
			} else {
				$error_code = "保存失败！";
			}
		}	
		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="modifyclassify"){
	$classname = post("classname");
	if(empty($classname)){
		$error_code = "分类名称不能为空！";
	}elseif(strlen($classname)>18){
		$error_code = "分类名称不能大于6个字！";
	}else{
		$sql = "select * from ".TABLE."account_class where classname='$classname' and classid<>'$_POST[classid]' and ufid='$userid'";
		$query = mysqli_query($conn,$sql);
		$attitle = is_array($row = mysqli_fetch_array($query));
		if($attitle){
			$error_code = "该名称的分类已经存在！";
		}else{
			$sql = "UPDATE ".TABLE."account_class set classname='$classname' , classtype=$_POST[classtype] where ufid='$userid' and classid=".$_POST["classid"];
			$query = mysqli_query($conn,$sql);
			if($query){
				$error_code = "保存成功！";
				$success = "1";
				$gotourl = "classify.php";
			}else{
				$error_code = "保存失败！";
			}
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="changeuser"){
	header('Content-type:text/html;charset=utf-8');
	$type = get("m");
	$uid = get("uid");
	if(empty($type) or empty($uid)){
		$error_code = "参数不完整！";
	}else{
		if($type=="noallow"){
			$u_sql = "Isallow=1";
			$itlu_word = "禁用";
		}else{
			$u_sql = "Isallow=0";
			$itlu_word = "启用";
		}
		$update_sql = "update ".TABLE."user set ".$u_sql." where uid='$uid'";
        $update_query = mysqli_query($conn,$update_sql);
		if($update_query){
			$error_code = $itlu_word."成功！";
		}else{
			$error_code = "操作失败！";
		}
	}
	echo $error_code;
}
if($getaction=="updateuser"){
	$password = post_pass("password");
	$newpassword = post_pass("newpassword");
	$email = post("email");
	if(empty($password)){
		$error_code = "密码不能为空！";
	}elseif(strlen($password)<6){
		$error_code = "密码不能小于6个字符！";
	}elseif((!empty($email)) && (checkemail($email) == false)){
		$error_code = "邮箱格式错误！";
	}elseif(!empty($newpassword) && strlen($newpassword)<6){
		$error_code = "新密码不能小于6个字符！";
	}else{
		$sql = "SELECT * FROM ".TABLE."user where uid='$userid'";
		$query = mysqli_query($conn,$sql);
		$row = mysqli_fetch_array($query);
		$db_salt = $row["salt"];
		if($row["password"] == hash_md5($password,$db_salt)){			
			$update_time = strtotime("now");
			$u_sql = "utime='$update_time'";
			if(!empty($email)){
				$u_sql = $u_sql.",email='$email'";
			}
			if(!empty($newpassword)){				
				$salt = md5($row["username"].$update_time.$newpassword);
				$user_pass = hash_md5($newpassword,$salt);
				$u_sql = $u_sql.",password='$user_pass',salt='$salt'";
			}
			$update_sql = "update ".TABLE."user set ".$u_sql." where uid='$userid'";
            $update_query = mysqli_query($conn,$update_sql);
			if($update_query){
				$success = "1";
				$userinfo_update = array("userid"=>"$userid","username"=>"$row[username]","useremail"=>"$email","regtime"=>"$row[addtime]","updatetime"=>"$update_time","isadmin"=>"$row[Isadmin]");
				$userinfo = AES::encrypt($userinfo_update, $sys_key);
				setcookie("userinfo", $userinfo, time()+86400);
				$error_code = "信息修改成功！";
				$gotourl = "users.php";
			}else{
				$error_code = "出错啦，写入数据库时出错！";
			}
		}else{
			$error_code = "旧密码错误！";
		}		
	}
	$data = '{code:"' .$success. '",error_msg:"' .$error_code.'",url:"' .$gotourl.'"}';
    echo json_encode($data);
}
if($getaction=="export"){
	header('Content-type:text/html;charset=utf-8');
	$sql = "select a.zhifu,a.acmoney,a.actime,a.acremark,b.classname from ".TABLE."account as a INNER JOIN ".TABLE."account_class as b ON b.classid=a.acclassid and a.jiid='$userid'";
	$result = mysqli_query($conn,$sql);
    $str = "分类,收支,金额,时间,备注\n";
    $str = iconv('utf-8','gb2312',$str);
    while ($row = mysqli_fetch_array($result)) {
        $classname = iconv('utf-8','gb2312',$row['classname']);
        if ($row['zhifu'] == 1) {
            $shouzhi = iconv('utf-8','gb2312',"收入");
        } else {
            $shouzhi = iconv('utf-8','gb2312',"支出");
        }
        $money = $row['acmoney'];
        $time = date("Y-m-d H:i",$row['actime']);		
		if($row['acremark']<>""){
			$remark = iconv('utf-8','gb2312',$row['acremark']);
		}else{
			$remark = "";
		}        
        $str .= $classname.",".$shouzhi.",".$money.",".$time.",".$remark."\n";
    }
    $filename = date('Ymd').'.csv';
    export_csv($filename,$str);
}
if($getaction=='import') {
	header('Content-type:text/html;charset=utf-8');
    if(empty($_FILES['file']['tmp_name'])){alertgourl("请选择文件！","int_out.php");}	
    $filename = $_FILES['file']['tmp_name'];
    $handle = fopen($filename, 'r');
    $result = input_csv($handle);
    $len_result = count($result);
    if ($len_result == 0){alertgourl("你的文件没有任何数据！","int_out.php");}
	$data_values = "";
    for ($i = 1; $i < $len_result; $i++) {
        $classify = iconv('gb2312', 'utf-8', $result[$i][0]);
        $shouzhi = iconv('gb2312', 'utf-8', $result[$i][1]);
        if ($shouzhi == "收入") {
            $shouzhi = "1";
        }else{
            $shouzhi = "2";
        }
        $sql = "select classid from ".TABLE."account_class where classname='$classify' and ufid='$userid'";
        $query = mysqli_query($conn,$sql);
		$row = mysqli_fetch_array($query);
        if ($row["classid"]){
			$acclassid = $row["classid"];
        }else{
            $sqladd = "insert into ".TABLE."account_class (classname, classtype, ufid) values ('$classify', '$shouzhi', '$userid')";
            $queryadd = mysqli_query($conn,$sqladd);
            $acclassid = mysqli_insert_id($conn);
        }
        $money = $result[$i][2];
        $addtime = strtotime($result[$i][3]);
        $remark = iconv('gb2312', 'utf-8', $result[$i][4]);        
        $data_values .= "('$money','$acclassid','$addtime','$remark','$userid','$shouzhi'),";
    }
    $data_values = substr($data_values,0,-1);
    fclose($handle);
    $query = mysqli_query($conn,"insert ".TABLE."account (acmoney,acclassid,actime,acremark,jiid,zhifu) values $data_values");
    if($query){        
		$word = "导入成功！导入".$len_result."条";
		alertgourl($word,"int_out.php");
    }else{
		alertgourl("导入失败，请检查文件格式！","int_out.php");
    }
}
?>