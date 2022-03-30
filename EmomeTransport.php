<?php

namespace WakeaspInc\Emome;

use Emome\SMSClient;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Quentin Dequippe <quentin@dequippe.tech>
 */
final class EmomeTransport extends AbstractTransport
{
    protected const HOST = "emome";

    private $account;
    private $password;

    public function __construct(string $account, string $password, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->account = $account;
        $this->password = $password;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {

        return sprintf('emome://%s', $this->getEndpoint());
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        $client = new SMSClient($this->account,$this->password);

        try {
            $response = $client->send($message->getSubject(),$message->getPhone());
            $statusCode = $response->getStatusCode();
        } catch (TransportExceptionInterface $e) {
            throw new TransportException('Could not reach the remote Emome server.', $response, 0, $e);
        }

        $responseContents = $client->parseResponse($response->getContent());

        if (200 !== $statusCode) {
            throw new TransportException(sprintf('Unable to send the SMS: "%s" (%s).', $responseContents['description'], $responseContents['code']), $response);
        }


        if (false === isset($responseContents['message_id'])) {
            throw new TransportException(sprintf('Unable to send the SMS: "%s" (%s).', $responseContents['description'], $responseContents['code']), $response);
        }

        $sentMessage = new SentMessage($message, (string)$this);
        $sentMessage->setMessageId($responseContents['message_id']);

        return $sentMessage;
    }
}