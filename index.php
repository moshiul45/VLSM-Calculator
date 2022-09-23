<?php
session_start();

$cn_value=4;$sub_number_err="";$a=0;
$all_sub=[];$all_cidr;$all_sub_mask;$all_network;$all_broadcast;
$host_name;$host_number; $total_host;$available_host;$ip_range;

if($_SERVER['REQUEST_METHOD']=="POST"){
	if(isset($_POST['btn_change']))
	{
		if(empty($_POST['sub_number']))
		{
			$sub_number_err="SUBNET NUMBER CAN NOT BE EMPTY";
		}
		else
		{
			$cn_value=$_POST['sub_number'];
			$_SESSION['cn_value']=$cn_value;	
		}
		
	}
 //////////////////////////////////////////////////////////////////////////

	if(isset($_POST['btn_calculate']))
	{
//         $all_sub=$_POST;
//         $i=1;
// 		foreach ($all_sub as $key => $value) {
// 		if($key['host'].$key['$i']){
// 		echo  $key['host'].$key['$i'].$value;
// 		echo "<br>";
// 		$i++;
// 	      }
	

  

  //using for dynamic change of input field stroing the value of total subnets
    if(empty($_SESSION['cn_value']))
    {
         $cn_value=4;
    }else
    {
      $cn_value=$_SESSION['cn_value'];
   
     session_unset();
     session_destroy();
    }
  //////////////////////////////////////////////////////////////////////////
  		  
        for ($i=1; $i <=$cn_value ; $i++) { 
		        $host_name[$i]="host".$i;
		        $host_number[$i]="host_number".$i;	
		  }

// } 


    //all value assigning in all_sub based on input
		  for ($i=1; $i <=$cn_value ; $i++) {		 
		       $all_sub[$host_name[$i]]=(int)$_POST[$host_number[$i]];	
		  }
      
      $total_host=array_sum($all_sub)+$cn_value*2;//total number of host input by the user 
      $available_host=pow(2,(32-$_POST['cidr']));//from the slash of the main network
    
    if($available_host>=$total_host){
       
       $a=1;//for showing column heading 
       arsort($all_sub);
       $i=0;
       foreach ($all_sub as $key => $value) {
       		
          if(!empty($value)){
          $all_cidr[$i]=calculate_cidr($value);
       		$i++;
        }
          else{
            trigger_error("host number can not be empty or zero"."please check ".$key." Host number");
            $a=0;
          }

       }
          //calling subnet mask calculation function
         calculate_subnet();
         //calling network & broadcast ip calculation function
         calculate_network_brodcast_Id($_POST['main_ip'],$_POST['cidr']);
         //calling the usable ip range calculating funcion
         calulate_usable_ip_range();
     
     }else{
      echo "SUBNETTING NOT POSSIBLE.AVAILABLE HOST-".$available_host."HOST NEEDED-".$total_host;
      $a=0;
     }
   }

    //    //printing host name and number 
    //   foreach ($all_sub as $key => $value) {
    //     echo $key.'=>'.$value;
    //   }
       
    //   // printing nid 
    //     for ($j=0; $j <sizeof($all_network); $j++) { 
    //    echo $all_network[$j];
    //    echo "<br>";
    //   } 

    //    // printing broadcast ip

    //   for ($j=0; $j <sizeof($all_broadcast); $j++) { 
    //    echo $all_broadcast[$j];
    //    echo "<br>";
    //   }

    //    //printing all cidr value
  		
    //   for ($j=0; $j <sizeof($all_cidr); $j++) { 
  		// 	echo $all_cidr[$j];
  		// 	echo "<br>";
  		// }
    //     //printing all subnet mask
    //     for ($j=0; $j <sizeof($all_sub_mask); $j++) { 
  		// 	echo $all_sub_mask[$j];
  		// 	echo "<br>";
  		// }
}
 //////////////////////////////////////////////////////////////////////////


//calcullating cidr value
    function calculate_cidr($host_number){
       		//for ($i=0; $i < 4; $i++) { 
       			for($j=0;$j<100;$j++){
       				if((pow(2,$j)-2)>=$host_number){
       					return 32-$j;// for host number 0 validation
       					break;
       				}
       			}
       		//}
       }
  

//calculating subnet mask from cidr value       
       function calculate_subnet(){
       	global $all_cidr,$all_sub_mask;
       	$s1=$s2=$s3=$s4="";
       	for ($i=0; $i <sizeof($all_cidr) ; $i++) { 
       		$test=$all_cidr[$i];
       		{
       			for ($j=1; $j<=$test ; $j++) { 
       				if($j<=8){
                          $s1=$s1.'1';
       				}
       				if( $j>8 && $j<=16){
                          $s2=$s2.'1';
       				}
       				if($j>16 && $j<=24 ){
       					$s3=$s3.'1';
       				}
       				if( $j>24 && $j<=32 ){
       					$s4=$s4.'1';
       				}
       			}if(empty($s1))
                    {
                    	$s1='00000000';
                    }
                    if(empty($s2))
                    {
                    	$s2='00000000';
                    }
                    if(empty($s3))
                    {
                    	$s3='00000000';
                    }
                    if(empty($s4))
                    {
                    	$s4='00000000';
                    }
       		}
            if(strlen($s1)<8)
            {
            	for($k=strlen($s1);$k<8;$k++)
            	{
            		$s1=$s1.'0';
            	}

            }if(strlen($s2)<8)
            {
            	for($k=strlen($s2);$k<8;$k++)
            	{
            		$s2=$s2.'0';
            	}

            }if(strlen($s3)<8)
            {
            	for($k=strlen($s3);$k<8;$k++)
            	{
            		$s3=$s3.'0';
            	}

            }if(strlen($s4)<8)
            {
            	for($k=strlen($s4);$k<8;$k++)
            	{
            		$s4=$s4.'0';
            	}

            }
       		$all_sub_mask[$i]=bindec($s1).".".bindec($s2).".".bindec($s3).".".bindec($s4);//assign them in a array
       		
       		$s1=$s2=$s3=$s4="";//optional test purpose 
       	}        
       }

       function calculate_network_brodcast_Id($main_ip,$cidr)
       {
        global $all_cidr,$all_network,$all_broadcast;
        $s1=$s2=$s3=$s4=$b1=$b2=$b3=$b4="";
        $s=explode(".",$main_ip);
        $s1=(int)$s[0];
        $s2=(int)$s[1];
        $s3=(int)$s[2]; 
        $s4=(int)$s[3];
        
        

        for ($i=0; $i <sizeof($all_cidr) ; $i++) {
         if($i==0)
         {
            $test=(int)$cidr;          

            for ($j=1; $j<=$test ; $j++) {              
              if($j<=8){
                          $b1=$b1.'1';                          
              }
              if( $j>8 && $j<=16){
                          $b2=$b2.'1';
              }
              if($j>16 && $j<=24 ){
                $b3=$b3.'1';
              }
              if( $j>24 && $j<=32 ){
                $b4=$b4.'1';
              }
            }if(strlen($b1)<8)
            {
              for($k=strlen($b1);$k<8;$k++)
              {
                $b1=$b1.'0';
              }

            }if(strlen($b2)<8)
            {
              for($k=strlen($b2);$k<8;$k++)
              {
                $b2=$b2.'0';
              }

            }if(strlen($b3)<8)
            {
              for($k=strlen($b3);$k<8;$k++)
              {
                $b3=$b3.'0';
              }

            }if(strlen($b4)<8)
            {
              for($k=strlen($b4);$k<8;$k++)
              {
                $b4=$b4.'0';
              }
            }
                        
            $a=decbin($s1);
            if(strlen($a)<8){
              for($k=strlen($a);$k<8;$k++)
              {
                $a='0'.$a;
              }
            }
            
            $b=decbin($s2);
            if(strlen($b)<8){
              for($k=strlen($b);$k<8;$k++)
              {
                $b='0'.$b;
              }
            }

            $c=decbin($s3);
            if(strlen($c)<8){
              for($k=strlen($c);$k<8;$k++)
              {
                $c='0'.$c;
              }
            }
            
            $d=decbin($s4);
            if(strlen($d)<8){
              for($k=strlen($d);$k<8;$k++)
              {
                $d='0'.$d;
              }
            }
          
            $s1=$s2=$s3=$s4="";
            
            for ($l=0; $l <8; $l++) { 
              $s1=$s1.(int)$b1[$l]*(int)$a[$l];
            }
            for ($l=0; $l <8; $l++) { 
              $s2=$s2.(int)$b2[$l]*(int)$b[$l];
            }
            for ($l=0; $l <8; $l++) { 
              $s3=$s3.(int)$b3[$l]*(int)$c[$l];
            }
            for ($l=0; $l <8; $l++) { 
              $s4=$s4.(int)$b4[$l]*(int)$d[$l];
            }
            $s1=bindec($s1);
            $s2=bindec($s2);
            $s3=bindec($s3);
            $s4=bindec($s4);

            $all_network[$i]=$s1.".".$s2.".".$s3.".".$s4;
            $b1=$b2=$b3=$b4="";

            $test=(int)$all_cidr[$i];
            $test=32-$test;
            for ($j=1; $j<=$test ; $j++) {              
              if($j<=8){
                          $b4=$b4.'1';                          
              }
              if( $j>8 && $j<=16){
                          $b3=$b3.'1';
              }
              if($j>16 && $j<=24 ){
                $b2=$b2.'1';
              }
              if( $j>24 && $j<=32 ){
                $b1=$b1.'1';
              }
            }
             
             if(!empty($b4)){    
             $s4 +=bindec($b4);
             }
             if(!empty($b3)){
              $s3 +=bindec($b3);
             }
             if(!empty($b2)){
             $s2 +=bindec($b2);
             }
             if(!empty($b1)){
              $s1 +=bindec($b1);
             }    
             
        $all_broadcast[$i]=$s1.".".$s2.".".$s3.".".$s4;
        
   }
    else{
      
      $s=explode(".",$all_broadcast[$i-1]);
        $s1=(int)$s[0];
        $s2=(int)$s[1];
        $s3=(int)$s[2];
        $s4=(int)$s[3];
        if($s4+0==255)
        {
          $s4=0;
          if($s3+0==255)
          {
            $s3=0;
            if($s2+0==255)
            {
              $s2=0;
              if($s1+0==255)
              {
                $s1=0;
              }
              else
              {
                $s1 +=1;
              }

            }
            else{
            $s2 +=1;
            }
          }
          else{
            $s3 +=1;
          }         
        }
        else
        {
           $s4 +=1; 
        }

      $all_network[$i]=$s1.".".$s2.".".$s3.".".$s4;
      $s1=$s2=$s3=$s4=$b1=$b2=$b3=$b4="";
     
      $s=explode(".",$all_network[$i]);
        $s1=$s[0];
        $s2=$s[1];
        $s3=$s[2];
        $s4=$s[3];

        $test=$all_cidr[$i];
            $test=32-$test;
            for ($k=1; $k<=$test ; $k++) { 
              
              if($k<=8){
                          $b4=$b4.'1';
              }
              if( $k>8 && $k<=16){
                          $b3=$b3.'1';
              }
              if($k>16 && $k<=24 ){
                $b2=$b2.'1';
              }
              if( $k>24 && $k<=32 ){
                $b1=$b1.'1';
              }
             }
             
             if(!empty($b4)){
             $s4 +=bindec($b4);
             }
             if(!empty($b3)){
              $s3 +=bindec($b3);
             }
             if(!empty($b2)){
             $s2 +=bindec($b2);
             }
             if(!empty($b1)){
              $s1 +=bindec($b1);
             }  
              
             
        $all_broadcast[$i]=$s1.".".$s2.".".$s3.".".$s4;
        
  }       
         }
}

function calulate_usable_ip_range(){
  global $all_network,$all_broadcast,$ip_range;
  $s1=$s2=$s3=$s4=$b1=$b2=$b3=$b4="";
  for ($i=0; $i <sizeof($all_network) ; $i++) { 
    $s=explode(".",$all_network[$i]);
        $s1=$s[0];
        $s2=$s[1];
        $s3=$s[2];
        $s4=$s[3];

    $s4 +=1;

    $b=explode(".",$all_broadcast[$i]);
        $b1=$b[0];
        $b2=$b[1];
        $b3=$b[2];
        $b4=$b[3];

    $b4 -=1;

    $ip_range[$i]=$s1.".".$s2.".".$s3.".".$s4.'-'.$b1.".".$b2.".".$b3.".".$b4;
  }
}


?>
<!DOCTYPE html>
<html>
<head>
	<title>VLSM CALCULATOR</title>
</head>
<body>
<form method="POST" action="vlsm.php">
       	<label>HOW MANY SUBNET DO YOU NEED?</label>
       	<select style="width: 180px;height: 30px" name="sub_number" value="<?php echo $cn_value; ?>">
       		<option value="" selected disabled>please Select an option</option>
       		<option value="1">1</option>
       		<option value="2">2</option>
			    <option value="3">3</option>
       		<option value="4">4</option>
       		<option value="5">5</option>
       		<option value="6">6</option>
       		<option value="7">7</option>
       		<option value="8">8</option>
          <option value="9">9</option>
          <option value="10">10</option>
          <option value="11">11</option>
          <option value="12">12</option>
          <option value="13">13</option>
          <option value="14">14</option>
          <option value="15">15</option>
          <option value="16">16</option>
       	</select> 
       	<input type="submit" name="btn_change" value="CHANGE" style="width: 120px;height: 40px"><?php echo $sub_number_err;?>
        <br>
       	<label>Ip</label>
       	<input type="text" name="main_ip" placeholder=" e.g 192.168.0.1">
       	<label>/</label>
       	<input type="text" name="cidr" placeholder="e.g 26">


       	<table align="center">
       		<thead>
       			<tr align="center">
       				<td>HOST NAME</td>
       				<td>HOST NUMBER</td>
       			</tr>
       		</thead>
       		
            
            <?php
            for ($i=1; $i <=$cn_value; $i++) {?>
	           <tr>
	           	<td><input type="text" name="<?php echo "host".$i ?>" value="<?php echo "host".$i ?>"></td>
	           	<td><input type="text" name="<?php echo "host_number".$i ?>"></td>
	           </tr>
            
             <?  }?>
             <tr align="center">
             	<td colspan="2"><input type="submit" name="btn_calculate" value="CALCULATE"></td>
             </tr>
       	</table>
</form>
<table border="SOLID">
<?php if($a==1){?>
  <tr>
      <thead>
        <td>NAME</td>
        <td>HOST NEEDED</td>
        <td>NETWORK ADDRESS</td>
        <td>CIDR</td>
        <td>SUBNET MASK</td>
        <td>USABLE RANGE</td>
        <td>BROADCAST ADDRESS</td>
      </thead>
    </tr>

  <?php
  $i=0;
  foreach ($all_sub as $key => $value) {?>    
    <tr>
      <td><?php echo $key; ?></td>
      <td><?php echo $value; ?></td>
      <td><?php echo $all_network[$i]; ?></td>
      <td><?php echo $all_cidr[$i]; ?></td>
      <td><?php echo $all_sub_mask[$i]; ?></td>
      <td><?php echo $ip_range[$i]; ?></td>
      <td><?php echo $all_broadcast[$i]; ?></td>     
    </tr>
  <?php $i++;} ?>
  <?php } ?>
</table>
</body>
</html>
