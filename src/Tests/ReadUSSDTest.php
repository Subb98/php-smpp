<?php

namespace PhpSmpp\Tests;

use PhpSmpp\Client;
use PhpSmpp\Pdu\DeliverSm;
use PhpSmpp\Pdu\Pdu;
use PhpSmpp\Service\Listener;
use PhpSmpp\SMPP;
use PhpSmpp\Transport\FakeTransport;
use PHPUnit\Framework\TestCase;

class ReadUSSDTest extends TestCase
{
    /**
     * Проверяет успешную обработку входящего PDU с USSD-запросом.
     */
    public function testReadUSSD(): void
    {
        $service = new Listener([], '', '', Client::BIND_MODE_TRANSCEIVER);
        $service->client->debug = true;
        $service->client->setTransport(new FakeTransport());

        /** @var FakeTransport $transport */
        $transport = $service->client->getTransport();
        $transport->enqueueDeliverReceiptSm('deliver_sm_enquire_link'); // добавим enquire link до ussd
        $transport->enqueueDeliverReceiptSm('deliver_sm_ussd_ok'); // добавим ussd

        $service->listenOnce(function (Pdu $pdu) {
            $this->assertInstanceOf(DeliverSm::class, $pdu);

            /** @var DeliverSm $pdu */
            $this->assertEquals('992000338188', $pdu->source->value);
            $this->assertEquals('992909970010', $pdu->destination->value);
            $this->assertEquals('*3322#', $pdu->message);
            $this->assertEquals(SMPP::DELIVER_SM, $pdu->id);
            $this->assertEquals(1704, $pdu->sequence);
            $this->assertEquals(SMPP::ESME_ROK, $pdu->status);
        });
    }

    /**
     * Проверяет обработку входящего PDU с USSD-запросом, на который в реальных условиях не был получен ответ.
     */
    public function testReadUSSDNoResp(): void
    {
        $service = new Listener([], '', '', Client::BIND_MODE_TRANSCEIVER);
        $service->client->debug = true;
        $service->client->setTransport(new FakeTransport());

        /** @var FakeTransport $transport */
        $transport = $service->client->getTransport();
        $transport->enqueueDeliverReceiptSm('deliver_sm_enquire_link'); // добавим enquire link до ussd
        $transport->enqueueDeliverReceiptSm('deliver_sm_ussd_no_resp'); // добавим ussd

        $service->listenOnce(function (Pdu $pdu) {
            $this->assertInstanceOf(DeliverSm::class, $pdu);

            /** @var DeliverSm $pdu */
            $this->assertEquals('992000338188', $pdu->source->value);
            $this->assertEquals('992909970010', $pdu->destination->value);
            $this->assertEquals('*3322#', $pdu->message);
            $this->assertEquals(SMPP::DELIVER_SM, $pdu->id);
            $this->assertEquals(1, $pdu->sequence);
            $this->assertEquals(SMPP::ESME_ROK, $pdu->status);
        });
    }
}
