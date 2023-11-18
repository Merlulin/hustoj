<?php require("admin-header.php");
require_once("../include/const.inc.php");
if (!(isset($_SESSION[$OJ_NAME.'_'.'administrator']))){
        echo "<a href='../loginpage.php'>Please Login First!</a>";
        exit(1);
}?>
<?php if(isset($_POST['do'])){
        require_once("../include/check_post_key.php");
        if (isset($_POST['rjpid'])){
                $rjpid=intval($_POST['rjpid']);
                if($rjpid == 0) {
                    echo "Rejudge Problem ID should not equal to 0";
                    exit(1);
                }
                $sql="UPDATE `solution` SET `result`=1 WHERE `problem_id`=? and problem_id>0";
                pdo_query($sql,$rjpid) ;
                $sql="delete from `sim` WHERE `s_id` in (select solution_id from solution where `problem_id`=?)";
                pdo_query($sql,$rjpid) ;
                $url="../status.php?problem_id=".$rjpid;
                echo "Rejudged Problem ".$rjpid;
                echo "<script>location.href='$url';</script>";
        }else if (isset($_POST['rjsid'])){
                $rjsid=intval($_POST['rjsid']);
                $sql="delete from `sim` WHERE `s_id`=?";
                pdo_query($sql,$rjsid) ;
                $sql="UPDATE `solution` SET `result`=1 WHERE `solution_id`=? and problem_id>0" ;
                pdo_query($sql,$rjsid) ;
                $sql="select contest_id from `solution` WHERE `solution_id`=? " ;
                $data=pdo_query($sql,$rjsid);
                $row=$data[0];
                $cid=intval($row[0]);
                if ($cid>0)
                        $url="../status.php?cid=".$cid."&top=".($rjsid+1);
                else
                        $url="../status.php?top=".($rjsid+1);
                echo "Rejudged Runid ".$rjsid;
                echo "<script>location.href='$url';</script>";
        }else if (isset($_POST['result'])){
                $result=intval($_POST['result']);
                $to=intval($_POST['to']);
                $sql="UPDATE `solution` SET `result`=$to WHERE `result`=? and problem_id>0" ;
                pdo_query($sql,$result) ;
                $url="../status.php?jresult=$to";
                echo "<script>location.href='$url';</script>";
        }else if (isset($_POST['rjcid'])){
                $rjcid=intval($_POST['rjcid']);
		if( $rjcid>0 ){
			if(isset($_POST['pid'])){
				$pid=intval($_POST['pid']);
				$sql="UPDATE `solution` SET `result`=1 WHERE `contest_id`=? and num=?";
				pdo_query($sql,$rjcid,$pid) ;
			}else{
				$sql="UPDATE `solution` SET `result`=1 WHERE `contest_id`=? and problem_id>0";
				pdo_query($sql,$rjcid) ;
			}
			// 一旦重判某一竞赛，我们需要对竞赛的finished标签重置为0，这样使得后台check进程检测到该竞赛已结束，并且尚未影响竞赛积分，需要重新计算和更新各用户积分。
			// 需要注意的是，我们如果要重新排积分，那么我们就需要先覆盖掉之前该竞赛计算出来的积分，所以需要先用pre_rating 重新 赋值回rating，使得check进程在rating计算依据上一次的rating进行重新计算。
			
			$check_contest_has_count_rating_sql="SELECT count(1) FROM `contest_user` WHERE contest_id=?";
			// 仅当竞赛已经进行过rating更新才需要用pre_rating来覆盖rating
			$user_cnt=pdo_query($check_contest_has_count_rating_sql, $rjcid);
			$user_cnt=$user_cnt[0][0];
			if($user_cnt > 0){
				$reback_rating_sql="UPDATE `users` SET rating=pre_rating WHERE user_id IN (SELECT user_id FROM contest_user WHERE contest_id=?)";
				pdo_query($reback_rating_sql, $rjcid);
			}
			$rcount_rating_sql="UPDATE `contest` SET finished = 0 WHERE contest_id=?";
			pdo_query($rcount_rating_sql, $rjcid);

			$url="../status.php?cid=".($rjcid);
			echo "Rejudged Contest id :".$rjcid;
			echo "<script>location.href='$url';</script>";
		}else{
			echo "<script>location.href='rejudge.php';</script>";
		}
        }
        echo str_repeat(" ",4096);
        flush();
        if($OJ_REDIS){
           $redis = new Redis();
           $redis->connect($OJ_REDISSERVER, $OJ_REDISPORT);
           if(isset($OJ_REDISAUTH)) $redis->auth($OJ_REDISAUTH);
                $sql="select solution_id from solution where result=1 and problem_id>0";
                 $result=pdo_query($sql);
                 foreach($result as $row){
                        echo $row['solution_id']."\n";
                        $redis->lpush($OJ_REDISQNAME,$row['solution_id']);
                }
           $redis->close();
        }
        if (isset($OJ_UDP) && $OJ_UDP) {
           trigger_judge();
        }
}
?>
<div class="container">
<b>Rejudge</b>
        <ol>
        <li><?php echo $MSG_PROBLEM?>
        <form action='rejudge.php' method=post>
                <input type=input name='rjpid' placeholder="1001">      <input type='hidden' name='do' value='do'>
                <input type=submit value=submit>
                <?php require_once("../include/set_post_key.php");?>
        </form>
        <li><?php echo $MSG_SUBMIT?>
        <form action='rejudge.php' method=post>
                <input type=input name='rjsid' placeholder="1002">      <input type='hidden' name='do' value='do'>
                <input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
                <input type=submit value=submit>
        </form>
        <li><?php echo "$MSG_Manual"?>
        <form action='rejudge.php' method=post>
		<select name='result'>      
			<?php
				for($i=0;$i<4;$i++){
					echo "<option value='$i' ".($i==3?"selected":"").">".$jresult[$i]."</option>\n";
				}
	
			?>
		</select>
		<input type='hidden' name='do' value='do'> - &gt;
		<select name='to'>      
			<?php
				$i=1;
				echo "<option value='$i'>".$jresult[$i]."</option>\n";
				for($i=4;$i<14;$i++){
					echo "<option value='$i' ".($i==6?"selected":"").">".$jresult[$i]."</option>\n";
				}
	
			?>
		</select>
                <input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
                <input type=submit value=submit>
        </form>
        <li><?php echo $MSG_CONTEST?>
        <form action='rejudge.php' method=post>
                <input type=input name='rjcid' placeholder="1003" value='1003' > <input type='hidden' name='do' value='do'>
                <input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
                <input type=submit value=submit>
        </form>
        <form action='rejudge.php' method=post>
                <input type=input name='rjcid' placeholder="1004">
                <select name=pid >
<?php
                foreach($PID as $i=>$id){
                        echo "<option value='$i'>$id</option>";
                }

                ?>
                </select>
                <input type='hidden' name='do' value='do'>
                <input type=hidden name="postkey" value="<?php echo $_SESSION[$OJ_NAME.'_'.'postkey']?>">
                <input type=submit value=submit>
        </form>
</div>
