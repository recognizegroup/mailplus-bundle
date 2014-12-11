<?php

namespace Recognize\MailplusBundle\Service;

use Psr\Log\LoggerInterface;

/**
 * Class MailplusService
 * @package Recognize\MailplusBundle\MailplusService
 * @author Nick Obermeijer <n.obermeijer@recognize.nl>
 */
class MailplusService {

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $logger;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $options = array(
		'soap_version' => SOAP_1_1,
		'cache_wsdl' => WSDL_CACHE_NONE,
		'trace' => true
	);


	/**
	 * @param \Psr\Log\LoggerInterface $logger
	 * @param array $config
	 */
	public function __construct(LoggerInterface $logger, array $config) {
		$this->logger = $logger;
		$this->config = $config;
	}

	/**
	 * @return string
	 */
	protected function getMailplusId() {
		return $this->config['id'];
	}

	/**
	 * @return string
	 */
	protected function getMailplusPassword() {
		return $this->config['password'];
	}

	/**
	 * @return string
	 */
	protected function getContactsWsdl() {
		return $this->config['urls']['contacts'];
	}

	/**
	 * @param array $args
	 */
	protected function appendAuthentication(array &$args) {
		$args = array_merge(array('id' => $this->getMailplusId(), 'password' => $this->getMailplusPassword()), $args);
	}

	/**
	 * @param string $id
	 * @return array
	 * @throws \Exception
	 */
	public function getContact($id) {
		$this->logger->info('Mailplus.getContact', array($id));

		return $this->execute($this->getContactsWsdl(), 'getContactByExternalContactId', array('externalContactId' => $id));
	}

	/**
	 * @param int $id
	 * @param array $keys
	 * @param array $values
	 * @throws \Exception
	 * @return array
	 */
	public function updateContact($id, array $keys, array $values) {
		$this->logger->info('Mailplus.updateContact', array($id));

		return $this->execute($this->getContactsWsdl(), 'updateContact', array(
			'keys' => $keys,
			'values' => $values,
			'externalContactId' => $id,
			'visible' => true,
			'merge' => true
		));
	}

	/**
	 * @param array $keys
	 * @param array $values
	 * @return array
	 * @throws \Exception
	 */
	public function insertContact(array $keys, array $values) {
		$this->logger->info('Mailplus.insertContact');

		return $this->execute($this->getContactsWsdl(), 'updateContact', array('keys' => $keys, 'values' => $values));
	}

	/**
	 * @param array $keys
	 * @param array $values
	 * @return object
	 * @throws \Exception
	 */
	public function subscribeContact(array $keys, array $values) {
		$this->logger->info('Mailplus.subscribeContact');

		return $this->execute($this->getContactsWsdl(), 'subscribeContact', array('keys' => $keys, 'values' => $values, 'visible' => true));
	}

	/**
	 * @param string $wsdl
	 * @param string $method
	 * @param array $args
	 * @param array $inputHeaders
	 * @param array $outputHeaders
	 * @throws \Exception
	 * @return array
	 */
	protected function execute($wsdl, $method, array $args = array(), array $inputHeaders = array(), array $outputHeaders = array()) {
		try {

			$this->appendAuthentication($args); // Append mailplus login information

			$this->logger->info('Mailplus.execute', array($wsdl, $method, $args));

			$soapClient = new \SoapClient($wsdl, $this->options);

			$response = $soapClient->__soapCall($method, array($method => $args), $this->options, $inputHeaders, $outputHeaders);

			$this->logger->info('Mailplus.execute OK', array($response));

			return $response;

		} catch(\Exception $e) {
			$this->logger->error('Mailplus.execute FAIL', array($e->getMessage()));
			throw new \Exception($e->getMessage(), $e->getCode(), $e);
		}
	}

}