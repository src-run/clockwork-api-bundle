<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\ClockworkBundle\Component;

use Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\DependencyInjection\ContainerInterface;
use Scribe\ClockworkBundle\Exception\ClockworkException,
    Scribe\ClockworkBundle\Exception\ClockworkAPIException;

/**
 * Clockwork class
 */
class Clockwork implements ContainerAwareInterface
{
    /**
     * the base url for all api calls
     */
    const API_BASE_URL = 'api.clockworksms.com/xml/';

    /**
     * api method for authentication api calls
     */
    const API_METHOD_AUTH = 'authenticate';

    /**
     * api method for sms api calls
     */
    const API_METHOD_SMS = 'sms';

    /**
     * api method for checking message credit
     */
    const API_METHOD_CREDIT = 'credit';

    /**
     * api method for checking account balance
     */
    const API_METHOD_BALANCE = 'balance';

    /**
     * @var ContainerInterface
     */
    private $container = null;

    /**
     * @var string
     */
    private $api_key;

    /**
     * @var boolean
     */
    private $allow_long_messages;

    /**
     * @var boolean
     */
    private $truncate_long_messages;

    /**
     * @var string
     */
    private $from_address;

    /**
     * @var boolean
     */
    private $enable_ssl;

    /**
     * @var string
     */
    private $invalid_character_action;

    /**
     * @var boolean
     */
    private $log_activity;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->setContainer($container);
        
        $this->api_key                  = $container->getParameter('scribe_clockwork.api_key');
        $this->allow_long_messages      = $container->getParameter('scribe_clockwork.allow_long_messages');
        $this->truncate_long_messages   = $container->getParameter('scribe_clockwork.truncate_long_messages');
        $this->from_address             = $container->getParameter('scribe_clockwork.from_address');
        $this->enable_ssl               = $container->getParameter('scribe_clockwork.enable_ssl');
        $this->invalid_character_action = $container->getParameter('scribe_clockwork.invalid_character_action');
        $this->log_activity             = $container->getParameter('scribe_clockwork.log_activity');

        if ($this->truncate_long_messages === true) {
            $this->allow_long_messages = false;
        }
    }

    /**
     * @param  ContainerInterface $container
     * @return Clockwork
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @param  array $messages
     * @return array
     */
    public function sendMultiple(array $messages = null)
    {
        $returns = [];

        foreach ($messages as $phone => $message) {
            $returns[] = $this->send($phone, $message);
        }

        return $returns;
    }

    /**
     * @param  string $phone
     * @param  string $message
     * @return integer
     */
    public function send($phone, $message, $clientId = null)
    {
        $request = $this->createDocument();

        $root = $request->createElement('Message');
        $request->appendChild($root);

        $userNode = $request->createElement('Key');
        $userNode->appendChild($request->createTextNode($this->api_key));
        $root->appendChild($userNode);

        $smsNode = $request->createElement('SMS');

        if (!$this->isValidMSISDN($phone)) {
            throw new ClockworkException('The phone number must be a valid MSISDN');
        }

        $smsNode->appendChild($request->createElement('To', $phone));

        $contentNode = $request->createElement('Content');
        $contentNode->appendChild($request->createTextNode($message));
        $smsNode->appendChild($contentNode);

        if ($clientId !== null) {
            $clientIdNode = $request->createElement('ClientID');
            $clientIdNode->appendChild($request->createTextNode($clientId));
            $smsNode->appendChild($clientIdNode);
        }

        if (strlen($this->from_address) > 11) {
            $this->from_address = substr($this->from_address, 0, 11);
        }
        $fromNode = $request->createElement('From');
        $fromNode->appendChild($request->createTextNode($this->from_address));
        $smsNode->appendChild($fromNode);

        $longNode = $request->createElement('Long');
        $longNode->appendChild($request->createTextNode($this->allow_long_messages ? 1 : 0));
        $smsNode->appendChild($longNode);

        $truncateNode = $request->createElement('Truncate');
        $truncateNode->appendChild($request->createTextNode($this->truncate_long_messages ? 1 : 0));
        $smsNode->appendChild($truncateNode);

        if ($this->invalid_character_action == 'error') {
            $invalid_character_action_int = 1;
        } else if ($this->invalid_character_action == 'remove') {
            $invalid_character_action_int = 2;
        } else {
            $invalid_character_action_int = 3;
        }

        $smsNode->appendChild($request->createElement('InvalidCharAction', $invalid_character_action_int));

        $root->appendChild($smsNode);

        list($response, $error_number, $error_message)
            = $this->post(self::API_METHOD_SMS, $request)
        ;

        if ($error_number !== null || $error_message !== null) {
            throw new ClockworkAPIException($error_number, $error_message);
        }

        $message_id         = null;
        $sms_error_number  = null;
        $sms_error_message = null;

        foreach ($response->documentElement->childNodes as $child) {
            switch ($child->nodeName) {
                case 'SMS_Resp':
                    foreach ($child->childNodes as $smsChild) {
                        switch ($smsChild->nodeName) {
                            case 'MessageID':
                                $message_id = $smsChild->nodeValue;
                                break;
                            case 'ErrNo':
                                $sms_error_number = $smsChild->nodeValue;
                                break;
                            case 'ErrDesc':
                                $sms_error_message = $smsChild->nodeValue;
                                break;
                        }
                    }
                    break;
            }
        }

        if ($sms_error_number !== null || $sms_error_message !== null) {
            throw new ClockworkAPIException($sms_error_number, $sms_error_message);
        }

        return $message_id;
    }

    /**
     * @throws ClockworkException
     * @return boolean
     */
    public function isValidApiKey()
    {
        $request = $this->createDocument();

        $authenticate = $request->createElement('Authenticate');
        $authenticate->appendChild($request->createElement('Key', $this->api_key));
        $request->appendChild($authenticate);

        list($response, $error_number, $error_message)
            = $this->post(self::API_METHOD_AUTH, $request)
        ;

        if ($error_number !== null || $error_message !== null) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function checkCredit()
    {
        $request = $this->createDocument();

        $root = $request->createElement('Credit');
        $root->appendChild($request->createElement('Key', $this->api_key));
        $request->appendChild($root);

        list($response, $error_number, $error_message)
            = $this->post(self::API_METHOD_CREDIT, $request)
        ;

        if ($error_number !== null || $error_message !== null) {
            throw new ClockworkAPIException($error_number, $error_message);
        }

        $credit = 0;

        foreach ($response->documentElement->childNodes as $child) {
            switch ($child->nodeName) {
                case 'Credit':
                    $credit = $child->nodeValue;
                    break;
            }
        }

        return $credit;
    }

    /**
     * @return string
     */
    public function checkBalance()
    {
        $request = $this->createDocument();

        $root = $request->createElement('Balance');
        $root->appendChild($request->createElement('Key', $this->api_key));
        $request->appendChild($root);

        list($response, $error_number, $error_message)
            = $this->post(self::API_METHOD_BALANCE, $request)
        ;

        if ($error_number !== null || $error_message !== null) {
            throw new ClockworkAPIException($error_number, $error_message);
        }

        $symbol  = null;
        $code    = null;
        $balance = 0;

        foreach ($response->documentElement->childNodes as $child) {
            switch ($child->nodeName) {
                case 'Balance':
                    $balance = $child->nodeValue;
                    break;
                case 'Currency':
                    foreach ($child->childNodes as $currencyChild) {
                        switch ($currencyChild->tagName) {
                            case 'Symbol':
                                $symbol = $currencyChild->nodeValue;
                                break;
                            case 'Code':
                                $code = $currencyChild->nodeValue;
                                break;
                        }
                    }
                    break;
            }
        }

        return [
            $balance,
            $symbol,
            $code
        ];
    }

    /**
     * @throws ClockworkException
     * @param  string $method
     * @param  DOMDocument $document
     * @return array
     */
    private function post($method, \DOMDocument $document) 
    {
        $data     = $this->destroyDocument($document);
        $protocol = $this->enable_ssl ? 'https://' : 'http://';
        $url      = $protocol . self::API_BASE_URL . $method;

        if (!extension_loaded('curl')) {
            throw new ClockworkException('Clockwork requires the Curl PHP module is loaded');
        }

        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_POST,           1);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HTTPHEADER,     ["Content-Type: text/xml"]);
        curl_setopt($handle, CURLOPT_USERAGENT,      'ScribeClockworkBundle/1.0');
        curl_setopt($handle, CURLOPT_POSTFIELDS,     $data);

        $responseData = curl_exec($handle);
        $info         = curl_getinfo($handle);

        if ($responseData === false || $info['http_code'] !== 200 || curl_errno($handle) > 0) {
            throw new ClockworkException(
                'Error calling API (HTTP Code ' . ($info['http_code'] ? $info['http_code'] : 'None') . ') ' . curl_error($handle)
            );
        }

        curl_close($handle);

        $response = $this->createDocument($responseData);

        list($error_number, $error_message)
            = $this->documentParseError($response)
        ;

        return [
            $response,
            $error_number,
            $error_message
        ];
    }

    /**
     * @param  DOMDocument $document
     * @return array
     */
    private function documentParseError(\DOMDocument $document)
    {
        $error_number  = null;
        $error_message = null;

        foreach ($document->documentElement->childNodes as $child) {
            switch ($child->nodeName) {
                case 'ErrNo':
                    $error_number = $child->nodeValue;
                    break;
                case 'ErrDesc':
                    $error_message = $child->nodeValue;
                    break;
            }
        }

        return [
            $error_number,
            $error_message
        ];
    }

    /**
     * @param  null|string $data
     * @return DOMDocument
     */
    private function createDocument($data = null) 
    {
        $document = new \DOMDocument('1.0', 'UTF-8');

        if ($data !== null) {
            $document->loadXML($data);
        }

        return $document;
    }

    /**
     * @param  DOMDocument $document
     * @return string
     */
    private function destroyDocument(\DOMDocument $document)
    {
        return $document->saveXML();
    }

    /**
     * @param  string $msisdn
     * @return boolean
     */
    private function isValidMSISDN($msisdn)
    {
        return preg_match('/^[1-9][0-9]{7,12}$/', $msisdn);
    }
}