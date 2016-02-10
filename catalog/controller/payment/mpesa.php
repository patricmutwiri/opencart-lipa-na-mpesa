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
		$code = $request['code'];
		$code = strtoupper($code);
		$this->language->load('payment/mpesa');
		$this->load->model('checkout/order');	
		$orderid = $this->session->data['order_id'];
		$testcode = "SHOW COLUMNS FROM `".DB_PREFIX."order` LIKE 'mpesa_code'";
		$runcode = $this->db->query($testcode);
		//echo $runcode->num_rows;
		if (empty($runcode->num_rows)){ //test existence
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
		$orderid = $this->session->data['order_id'];
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
		$this->load->model('checkout/order');	

		$insert = $this->db->query("INSERT INTO " . DB_PREFIX . "ipn SET idTrx = '" . $id . "', origin = '" .$origin. "', destination = '" . $dest . "', timeStamp = '" . $tstamp . "', text = '" .$text . "', mpesaCode = '" .$mpesa_code. "',mpesaAccount = '" .$mpesa_acc. "',mpesaMSISDN = '" .$mpesa_msisdn. "',mpesaTrxDate = '" .$mpesa_trx_date. "',mpesaTrxTime = '" .$mpesa_trx_time. "',mpesaAmt = '" .$mpesa_amt. "',mpesaSender = '" .$mpesa_sender . "', ip = '" . $ip . "', sent = 0,orderid = '".$orderid."'");
		if($this->db->query($insert)) {
			echo 'success';
			//get order with this mpesacode
			//mark as paid
			//email

		} else {
			echo 'error';
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