<?php
class ControllerPaymentMpesa extends Controller {
	protected function index() {
		$this->language->load('payment/mpesa');

		$this->data['text_instruction'] = $this->language->get('text_instruction');
		$this->data['text_description'] = $this->language->get('text_description');
		$this->data['text_payment'] = $this->language->get('text_payment');

		$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->data['mpesa'] = nl2br($this->config->get('mpesa_' . $this->config->get('config_language_id')));

		$this->data['continue'] = $this->url->link('checkout/success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mpesa.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/mpesa.tpl';
		} else {
			$this->template = 'default/template/payment/mpesa.tpl';
		}	

		$this->render(); 
	}

	public function mpesa() {
		$request = $_REQUEST;
		$code = isset($request['code']) ? $_REQUEST['code'] : '';
		$code = strtoupper($code);
		$this->language->load('payment/mpesa');
		$this->load->model('checkout/order');	
		$orderid = $this->session->data['order_id'];
		$testcode = "SHOW COLUMNS FROM `".DB_PREFIX."order` LIKE 'mpesa_code'";
		$runcode = $this->db->query($testcode);
		if (empty($runcode->num_rows)){ //check existence
		    $addmpesacode = "ALTER TABLE ".DB_PREFIX ."order ADD mpesa_code varchar(50) NOT NULL";
		    $this->db->query($addmpesacode);
		} 
		if($this->db->query("UPDATE `" . DB_PREFIX . "order` SET mpesa_code = '" . $code . "' WHERE order_id = '" . (int)$orderid . "'")) {
			echo 'success';
		} else {
			echo 'error';
		}
	}	

	public function ipn() {
		$this->language->load('payment/mpesa');
		$this->load->model('checkout/order');	
		if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
         $url = $this->config->get('config_ssl');
      	} else {
         $url = $this->config->get('config_url');
      	}
      	$getorderid = 'SELECT * FROM '.DB_PREFIX.'order WHERE mpesa_code = "'.$_GET['mpesa_code'].'"';
      	$orderid = $this->db->query($getorderid);
      	if ($orderid->num_rows) {
	      		$orderid = $orderid->row['order_id'];
	      		$ipntable = "CREATE TABLE IF NOT EXISTS `".DB_PREFIX."ipn` (
						  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ai id',
						  `idTrx` varchar(50) NOT NULL,
						  `origin` varchar(50) NOT NULL,
						  `destination` varchar(50) NOT NULL,
						  `timeStamp` varchar(50) NOT NULL,
						  `text` varchar(60) NOT NULL,
						  `mpesaCode` varchar(50) NOT NULL,
						  `mpesaAccount` varchar(50) NOT NULL,
						  `mpesaMSISDN` varchar(50) NOT NULL,
						  `mpesaTrxDate` varchar(50) NOT NULL,
						  `mpesaTrxTime` varchar(50) NOT NULL,
						  `mpesaAmt` varchar(50) NOT NULL,
						  `mpesaSender` varchar(50) NOT NULL,
						  `ip` varchar(50) NOT NULL,
						  `sent` int(10) NOT NULL,
						  `orderid` varchar(20) NOT NULL,
						  PRIMARY KEY (`id`)
						) ENGINE=InnoDB DEFAULT CHARSET=latin1";
			$this->db->query($ipntable);
			$id 		= $_REQUEST['id']; //id
			$ip 		= isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';//ip
			$origin 	= $_GET['orig'];//origin of the transaction
			$dest 		= $_GET['dest'];//destination
			$tstamp 	= $_GET['tstamp'];//time stamp
			$text 		= $_GET['text'];//text
			$mpesa_code = $_GET['mpesa_code'];//mpesa code
			$mpesa_acc 	= $_GET['mpesa_acc'];//mpesa acc
			$mpesa_msisdn 	= $_GET['mpesa_msisdn'];//mpesa msisdn
			$mpesa_trx_date = $_GET['mpesa_trx_date'];//mpesa transaction date
			$mpesa_trx_time = $_GET['mpesa_trx_time'];//mpesa transaction time
			$mpesa_amt 		= $_GET['mpesa_amt'];//mpesa amount
			$mpesa_sender 	= $_GET['mpesa_sender'];//mpesa sender
			$mpesa_code = strtoupper($mpesa_code);
			$this->language->load('payment/mpesa');

			$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "ipn SET idTrx = '" . $id . "', origin = '" .$origin. "', destination = '" . $dest . "', timeStamp = '" . $tstamp . "', text = '" .$text . "', mpesaCode = '" .$mpesa_code. "',mpesaAccount = '" .$mpesa_acc. "',mpesaMSISDN = '" .$mpesa_msisdn. "',mpesaTrxDate = '" .$mpesa_trx_date. "',mpesaTrxTime = '" .$mpesa_trx_time. "',mpesaAmt = '" .$mpesa_amt. "',mpesaSender = '" .$mpesa_sender . "', ip = '" . $ip . "', sent = 0,orderid = '".$orderid."'");
			if($insert) {
				$order = $this->model_checkout_order->getOrder($orderid);
				//$language->load('mail/order');
				$subject = $order['store_name'].' Lipa na Mpesa  ';
				$mail = new Mail();
				$mail->protocol = $this->config->get('config_mail_protocol');
				$mail->parameter = $this->config->get('config_mail_parameter');
				$mail->hostname = $this->config->get('config_smtp_host');
				$mail->username = $this->config->get('config_smtp_username');
				$mail->password = $this->config->get('config_smtp_password');
				$mail->port = $this->config->get('config_smtp_port');
				$mail->timeout = $this->config->get('config_smtp_timeout');
				$mail->setTo($order['email']);
				$mail->setFrom($this->config->get('config_email'));
				$mail->setSender($order['store_name']);
				$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd"><html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>'.$subject.'</title></head><body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">';
				$html    .= '<div style="width:100%"> Hello <b>'.$order['firstname'].' '.$order['lastname'].'</b>, </div>';
				$comment =  ' Lipa na mpesa ';
				$url = $url . 'index.php?route=account/order/info&order_id=' . $order['order_id'];

				if($mpesa_amt < $order['total'] ) {
					$subject .= 'Amount Paid Less';
					$html    .= '<div style="width:100%"> Your <b><a href="'.$url.'">Order</a> </b> has been created and is still pending. ';
					$html    .= 'Please note that you have paid an amount less (<b>'.$order['currency_code'].' '.$mpesa_amt.'</b>) than the total order amount of ('.$order['currency_code'].' '.$order['total'].') expected. Please pay the full amount. </div>';
						//$comment .= ' Less amount paid ';
					$this->model_checkout_order->update($order['order_id'],2 , $comment, true);
				} elseif ($mpesa_amt > $order['total'] ) {
					$subject .= 'Amount Paid More';
					$refund = $mpesa_amt - $order['total'];
					$html    .= '<div> Your <b><a href="'.$url.'">Order</a> </b> has been created and is now complete. Please note that you have paid an amount more (<b>'.$mpesa_amt.'</b>) than the total order amount of ('.$order['total'].') expected. Please ask for a refund of ('.$order['currency_code'].' '. $refund.') </div>';
					$comment .= ' More amount paid';
					$this->model_checkout_order->update($order['order_id'],5 , $comment, true);
					//$this->model_checkout_order->confirm($order['order_id'],5 , $comment, true);
				} else {
					$subject .= ' Full Amount Paid ';
					$refund = $mpesa_amt - $order['total'];
					$html    .= '<div> Your <b><a href="'.$url .'">Order</a> </b> has been created and is still pending. Please note that you have paid the full order amount of ('.$order['total'].') expected. Thank You for shopping with us. </div>';
					$comment .= ' Full Amount Paid ';
					//$this->model_checkout_order->confirm($order['order_id'],5, $comment, true);
					$this->model_checkout_order->update($order['order_id'],5 , $comment, true);
				}
				$order = $this->model_checkout_order->getOrder($orderid);
				$html .= '<p> Order Status : '.$order['order_status'].'</p>';
				$html .= '</body></html>';
				$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
				$mail->setHtml($html);
				$mail->send();
				echo ' Success ';
			} else {
				echo ' Error';
			}
      	} else {
      		$orderid = $this->session->data['order_id'];
      	}
	}

	public function confirm() {
		$this->language->load('payment/mpesa');

		$this->load->model('checkout/order');

		$comment  = $this->language->get('text_instruction') . "\n\n";
		$comment .= $this->config->get('mpesa_' . $this->config->get('config_language_id')) . "\n\n";
		$comment .= $this->language->get('text_payment');

		$this->model_checkout_order->confirm($this->session->data['order_id'], $this->config->get('mpesa_order_status_id'), $comment, true);
	}
}
?>