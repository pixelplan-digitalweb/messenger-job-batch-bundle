<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\JobBatch;

use Symfony\Component\Uid\Uuid;

final class JobBatchIdGenerator
{
    public static function new(): JobBatchId
    {
        return JobBatchId::fromString(Uuid::v4()->jsonSerialize());
    }
}
