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

namespace Prooph\EventStoreClient\Common;

class SystemConsumerStrategies
{
    // Distributes events to a single client until it is full. Then round robin to the next client.
    public const DISPATCH_TO_SINGLE = 'DispatchToSingle';
    // Distribute events to each client in a round robin fashion.
    public const ROUND_ROBIN = 'RoundRobin';
    // Distribute events of the same streamId to the same client until it disconnects on a best efforts basis.
    // Designed to be used with indexes such as the category projection.
    public const PINNED = 'Pinned';
}
