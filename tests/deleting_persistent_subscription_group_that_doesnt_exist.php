<?php

/**
 * This file is part of `prooph/event-store-client`.
 * (c) 2018-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2018-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\EventStoreClient;

use Amp\Success;
use Generator;
use PHPUnit\Framework\TestCase;
use Prooph\EventStoreClient\Exception\InvalidOperationException;
use Prooph\EventStoreClient\Util\UuidGenerator;
use Throwable;

class deleting_persistent_subscription_group_that_doesnt_exist extends TestCase
{
    use SpecificationWithConnection;

    /** @var string */
    private $stream;

    protected function setUp(): void
    {
        $this->stream = UuidGenerator::generateWithoutDash();
    }

    protected function when(): Generator
    {
        yield new Success();
    }

    /**
     * @test
     * @throws Throwable
     */
    public function the_delete_fails_with_argument_exception(): void
    {
        $this->execute(function () {
            $this->expectException(InvalidOperationException::class);

            yield $this->conn->deletePersistentSubscriptionAsync(
                $this->stream,
                UuidGenerator::generateWithoutDash(),
                DefaultData::adminCredentials()
            );
        });
    }
}
