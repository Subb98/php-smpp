<?php

namespace PhpSmpp\Tests;

use PhpSmpp\Client;
use PhpSmpp\Service\Sender;
use PhpSmpp\Transport\FakeTransport;
use PHPUnit\Framework\TestCase;

class SendUSSDTest extends TestCase
{
    /**
     * Проверяет успешную обработку исходящего USSD-запроса.
     */
    public function testSendUSSD(): void
    {
        $service = new Sender([''], '', '', Client::BIND_MODE_TRANSCEIVER);
        $service->client->debug = true;
        $service->client->setTransport(new FakeTransport());
        $smsId = $service->sendUSSD('992000338188', 'Ваша заявка принята, дождитесь СМС подтверждения', '3322', []);
        $this->assertNotEmpty($smsId, 'Has no sms id');
    }
}
