<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WakeaspInc\Emome;

use WakeaspInc\Emome\EmomeTransport;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @author Quentin Dequippe <quentin@dequippe.tech>
 */
final class EmomeTransportFactory extends AbstractTransportFactory
{
    /**
     * @return EmomeTransport
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('emome' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'emome', $this->getSupportedSchemes());
        }

        $account = $this->getUser($dsn);
        $password = $this->getPassword($dsn);
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        return (new EmomeTransport($account, $password, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
    }

    protected function getSupportedSchemes(): array
    {
        return ['emome'];
    }
}