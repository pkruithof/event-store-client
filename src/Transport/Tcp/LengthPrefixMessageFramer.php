<?php
/**
 * This file is part of the prooph/event-store-client.
 * (c) 2018-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2018-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\EventStoreClient\Transport\Tcp;

use Prooph\EventStoreClient\Exception\InvalidArgumentException;
use Prooph\EventStoreClient\Exception\PackageFramingException;
use Prooph\EventStoreClient\SystemData\TcpPackage;

/** @internal */
class LengthPrefixMessageFramer
{
    /** @var int */
    private $maxPackageSize;
    /** @var string|null */
    private $messageBuffer;
    /** @var callable(string $bytes): void */
    private $receivedHandler;
    /** @var int */
    private $packageLength = 0;

    public function __construct(int $maxPackageSize = 64 * 1024 * 1024)
    {
        if ($maxPackageSize < 1) {
            throw new InvalidArgumentException('MaxPackageSize must be positive');
        }

        $this->maxPackageSize = $maxPackageSize;
    }

    public function reset(): void
    {
        $this->messageBuffer = null;
        $this->packageLength = 0;
    }

    public function unFrameData(string $data): void
    {
        if (null !== $this->messageBuffer) {
            $data = $this->messageBuffer . $data;
        }

        $dataLength = \strlen($data);

        if ($dataLength < TcpPackage::MandatorySize) {
            // message too short, let's wait for more data
            $this->messageBuffer = $data;

            return;
        }

        if (0 === $this->packageLength) {
            list(, $this->packageLength) = \unpack('V', \substr($data, 0, 4));
            $this->packageLength += TcpPackage::DataOffset;
        }

        if ($this->packageLength > $this->maxPackageSize) {
            throw new PackageFramingException(\sprintf(
                'Package size is out of bounds: %d (max: %d). This is likely an '
                . 'exceptionally large message (reading too many things) or there is'
                . 'a problem with the framing if working on a new client',
                $this->packageLength,
                $this->maxPackageSize
            ));
        }

        if ($dataLength === $this->packageLength) {
            ($this->receivedHandler)($data);

            $this->reset();
        } elseif ($dataLength > $this->packageLength) {
            $message = \substr($data, 0, $this->packageLength);

            ($this->receivedHandler)($message);

            $this->reset();

            $this->messageBuffer = \substr($data, $this->packageLength, $dataLength);
        } else {
            $this->messageBuffer = $data;
        }
    }

    /** @var callable(string $data): void */
    public function registerMessageArrivedCallback(callable $handler): void
    {
        $this->receivedHandler = $handler;
    }
}
