<?php

class RequestsTest_Auth_Digest extends PHPUnit_Framework_TestCase {
	public static function transportProvider() {
		$transports = array(
			array('Requests_Transport_fsockopen'),
			array('Requests_Transport_cURL'),
		);
		return $transports;
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testUsingArray($transport) {
		if (!call_user_func(array($transport, 'test'))) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = array(
			'auth_digest' => array('user', 'passwd'),
			'transport' => $transport,
		);
		$request = Requests::get('http://httpbin.org/digest-auth/auth/user/passwd', array(), $options);
		$this->assertEquals(200, $request->status_code);

		$result = json_decode($request->body);
		$this->assertEquals(true, $result->authenticated);
		$this->assertEquals('user', $result->user);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testUsingInstantiation($transport) {
		if (!call_user_func(array($transport, 'test'))) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = array(
			'auth_digest' => new Requests_Auth_Digest(array('user', 'passwd')),
			'transport' => $transport,
		);
		$request = Requests::get('http://httpbin.org/digest-auth/auth/user/passwd', array(), $options);
		$this->assertEquals(200, $request->status_code);

		$result = json_decode($request->body);
		$this->assertEquals(true, $result->authenticated);
		$this->assertEquals('user', $result->user);
	}

	/**
	 * @dataProvider transportProvider
	 */
	public function testPOSTUsingInstantiation($transport) {
		if (!call_user_func(array($transport, 'test'))) {
			$this->markTestSkipped($transport . ' is not available');
			return;
		}

		$options = array(
			'auth_digest' => new Requests_Auth_Digest(array('user', 'passwd')),
			'transport' => $transport,
		);
		$data = 'test';
		$request = Requests::post('http://httpbin.org/post', array(), $data, $options);
		$this->assertEquals(200, $request->status_code);

		$result = json_decode($request->body);

		$auth = $result->headers->Authorization;
		$auth = explode(' ', $auth);

		$this->assertEquals(base64_encode('user:passwd'), $auth[1]);
        $this->assertEquals('test', $result->data);
	}

	/**
	 * @expectedException Requests_Exception
	 */
	public function testMissingPassword() {
		$auth = new Requests_Auth_Digest(array('user'));
	}

}