<?php 
class ControllerPaymentMpesa extends Controller {
	private $error = array(); 

	public function index() {
		$this->language->load('payment/mpesa');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('mpesa', $this->request->post);				

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');

		$this->data['entry_mpesa'] = $this->language->get('entry_mpesa');
		$this->data['entry_total'] = $this->language->get('entry_total');	
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_dev'] = $this->language->get('entry_dev');

		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if (isset($this->error['mpesa_' . $language['language_id']])) {
				$this->data['error_mpesa_' . $language['language_id']] = $this->error['mpesa_' . $language['language_id']];
			} else {
				$this->data['error_mpesa_' . $language['language_id']] = '';
			}
		}

		$this->data['breadcrumbs'] = array();

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => false
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/mpesa', 'token=' . $this->session->data['token'], 'SSL'),
			'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/mpesa', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$this->load->model('localisation/language');

		foreach ($languages as $language) {
			if (isset($this->request->post['mpesa_' . $language['language_id']])) {
				$this->data['mpesa_' . $language['language_id']] = $this->request->post['mpesa_' . $language['language_id']];
			} else {
				$this->data['mpesa_' . $language['language_id']] = $this->config->get('mpesa_' . $language['language_id']);
			}
		}

		$this->data['languages'] = $languages;

		if (isset($this->request->post['mpesa_total'])) {
			$this->data['mpesa_total'] = $this->request->post['mpesa_total'];
		} else {
			$this->data['mpesa_total'] = $this->config->get('mpesa_total'); 
		} 

		if (isset($this->request->post['mpesa_order_status_id'])) {
			$this->data['mpesa_order_status_id'] = $this->request->post['mpesa_order_status_id'];
		} else {
			$this->data['mpesa_order_status_id'] = $this->config->get('mpesa_order_status_id'); 
		} 

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['mpesa_geo_zone_id'])) {
			$this->data['mpesa_geo_zone_id'] = $this->request->post['mpesa_geo_zone_id'];
		} else {
			$this->data['mpesa_geo_zone_id'] = $this->config->get('mpesa_geo_zone_id'); 
		} 

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['mpesa_status'])) {
			$this->data['mpesa_status'] = $this->request->post['mpesa_status'];
		} else {
			$this->data['mpesa_status'] = $this->config->get('mpesa_status');
		}

		if (isset($this->request->post['mpesa_sort_order'])) {
			$this->data['mpesa_sort_order'] = $this->request->post['mpesa_sort_order'];
		} else {
			$this->data['mpesa_sort_order'] = $this->config->get('mpesa_sort_order');
		}


		$this->template = 'payment/mpesa.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/mpesa')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		foreach ($languages as $language) {
			if (!$this->request->post['mpesa_' . $language['language_id']]) {
				$this->error['mpesa_' .  $language['language_id']] = $this->language->get('error_mpesa');
			}
		}

		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>