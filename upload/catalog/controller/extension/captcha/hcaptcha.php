<?php

class ControllerExtensionCaptchaHCaptcha extends Controller {
    public function index($error = array()) {
        $this->load->language('extension/captcha/hcaptcha');

        if (isset($error['h-captcha'])) {
			$data['error_captcha'] = $error['captcha'];
		} else {
			$data['error_captcha'] = '';
		}

		$data['site_key'] = $this->config->get('captcha_hcaptcha_key');
        $data['route'] = $this->request->get['route'];
        $data['lang'] = $this->language->get('code');

		return $this->load->view('extension/captcha/hcaptcha', $data);
    }

    public function validate() {
		if (empty($this->session->data['h-captcha'])) {
			$this->load->language('extension/captcha/hcaptcha');

			if (!isset($this->request->post['h-captcha-response'])) {
				return $this->language->get('error_captcha');
			}

			// Create query data array
			$query_data = [
                'secret' => $this->config->get('captcha_hcaptcha_secret'),
                'response' => $this->request->post['h-captcha-response'],
            ];

			if ($this->config->get('captcha_hcaptcha_secret'))
			    $query_data['remoteip'] = $this->request->server['REMOTE_ADDR'];

			// Create our request
            $verify = curl_init();
            curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
            curl_setopt($verify, CURLOPT_POST, true);
            curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($query_data));
            curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);

            // Execute verify query
            $response = curl_exec($verify);
            $responseData = json_decode($response);

            // When not success we return an error
			if (!$responseData->success) {
                return $this->language->get('error_captcha');
			}

            $this->session->data['h-captcha'] = true;
		}
    }
}
