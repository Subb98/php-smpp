<?php

namespace PhpSmpp\Service;

use PhpSmpp\Client;

class Listener extends Service
{
    public function bind()
    {
        $this->openConnection();
        if (Client::BIND_MODE_TRANSCEIVER == $this->bindMode) {
            $this->client->bindTransceiver($this->login, $this->pass);
        } else {
            $this->client->bindReceiver($this->login, $this->pass);
        }
    }

    /**
     * @param callable $callback \PhpSmpp\Pdu\Pdu passed as a parameter
     */
    public function listen(callable $callback)
    {
        while (true) {
            try {
                $this->listenOnce($callback);
                usleep(10e4);
            } catch (\Throwable $e) {
                if ($this->debug) {
                    call_user_func(
                        $this->debugHandler,
                        __METHOD__ . " exception: {$e->getCode()}, {$e->getMessage()}, {$e->getTraceAsString()}"
                    );
                }
            }
        }
    }

    public function listenOnce(callable $callback)
    {
        $this->enshureConnection();
        $this->client->listenSm($callback);
    }
}
