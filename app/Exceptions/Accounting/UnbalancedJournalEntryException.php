<?php

declare(strict_types=1);

namespace App\Exceptions\Accounting;

use DomainException;

final class UnbalancedJournalEntryException extends DomainException
{
}
