<?php require_once("admin-header.php");

if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']))){
  echo "<a href='../loginpage.php'>Please Login First!</a>";
  exit(1);
}

if(isset($OJ_LANG)){
  require_once("../lang/$OJ_LANG.php");
}
?>

<title>Add User</title>
<hr>
<center><h3><?php echo $MSG_USER."-".$MSG_SETRATING?></h3></center>

<div class='container'>

<?php
if(isset($_POST['do'])){
  require_once("../include/check_post_key.php");
  require_once("../include/my_func.inc.php");

  $pieces = explode("\n", trim($_POST['ulist']));
  //想支持中文，修改下行代码为：$pieces = preg_replace("/[^\x20-\x7e\x{4e00}-\x{9fa5}]/u", " ", $pieces);  //修改代码支持user_id为中文
  $pieces = preg_replace("/[^\x20-\x7e]/", " ", $pieces);  //!!important

  $ulist = "";
  if(count($pieces)>0 && strlen($pieces[0])>0){
    for($i=0; $i<count($pieces); $i++){
      $id_pw = explode(" ", trim($pieces[$i]));
      // 重置某一用户的积分
      $sql = "UPDATE `users` SET rating=700, pre_rating=700 WHERE user_id=?";
      pdo_query($sql, $id_pw[0]);  
    }
    echo "<br>用户积分已重置!<hr>";
  }
}
?>
<?php
if(isset($_POST['do_all'])){
  require_once("../include/check_post_key.php");
  require_once("../include/my_func.inc.php");
  // 重置所有用户的积分
  /*
  $confirm = "<script> return confirm('是否确认要重置所有用户的联赛积分?'); </script>";
  if($confirm){
    $sql = "UPDATE `users` SET rating=700, pre_rating=700";
    pdo_query($sql);    
    echo "<script>alter('已重置所有用户的联赛积分!')</script>"; 
  }else{
    echo "<script>alter('已取消重置操作!')</script>";
  }
  */
  $sql = "UPDATE `users` SET rating=700, pre_rating=700";
  pdo_query($sql);    
  echo "<script>alter('已重置所有用户的联赛积分!')</script>"; 
}
?>

<form action=user_rating.php method=post class="form-horizontal">
  <div>
    <label class="col-sm"><?php echo $MSG_USER_ID?></label>
  </div>
  <div>
    <?php echo "( Add user_id with newline &#92;n )"?>
    <br>
    <table width="100%">
      <tr>
        <td height="*">
          <p align=left>
            <textarea name='ulist' rows='10' style='width:100%;' placeholder='userid1<?php echo "\n"?>userid2<?php echo "\n"?>userid3<?php echo "\n"?>
            <?php echo $MSG_PRIVATE_USERS_RATING_EDIT?><?php echo "\n"?>'><?php if(isset($ulist)){ echo $ulist;}?></textarea>
          </p>
        </td>
      </tr>
    </table>
  </div>

  <div class="form-group">
    <?php require_once("../include/set_post_key.php");?>
    <div class="col-sm-offset-3 col-sm-2">
      <button name="do" type="hidden" value="do" class="btn btn-default btn-block" ><?php echo $MSG_SAVE?></button>
    </div>
    <div class="col-sm-2">
      <button name="submit" type="reset" class="btn btn-default btn-block">清空文本框</button>
    </div>
    <div class="col-sm-2">
      <button name="do_all" type="hidden" value="do_all" class="btn btn-default btn-block" onclick="return confirm('是否确定需要重置所有用户的联赛积分?')">重置所有用户积分</button>
    </div>
  </div>

</form>

</div>


