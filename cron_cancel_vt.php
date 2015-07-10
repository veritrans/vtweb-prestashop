<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

$sql = 'SELECT id_order FROM '._DB_PREFIX_.'orders WHERE current_state = (SELECT id_order_state FROM '._DB_PREFIX_.'order_state WHERE module_name = "veritranspay") AND date_add < TIMESTAMPADD(MINUTE,(12*60)-15,now())';


$results = Db::getInstance()->ExecuteS($sql);
if (!$results)
{	
	error_log('result null');
}  
else
{
	foreach ($results as $row)
	{	
		$history = new OrderHistory();
		$history->id_order = $row['id_order'];
		$order_id_notif = $row['id_order'];
		$history->changeIdOrderState( Configuration::get('PS_OS_CANCELED'), $order_id_notif);
		$history->addWithemail(true);
		
		/*$sql2 = "	INSERT INTO "._DB_PREFIX_."order_history (id_employee,id_order,id_order_state,date_add) 
					VALUES ('0','".$row['id_order']."','".Configuration::get('PS_OS_CANCELED')."',TIMESTAMPADD(MINUTE,(12*60),now()))
				";
  		Db::getInstance()->Execute($sql2);*/
	}
}
?>