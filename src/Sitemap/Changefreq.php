<?php

declare(strict_types=1);

namespace Eternium\Sitemap;

enum Changefreq: string implements \Stringable
{
    case Always = 'always';

    case Hourly = 'hourly';

    case Daily = 'daily';

    case Weekly = 'weekly';

    case Monthly = 'monthly';

    case Yearly = 'yearly';

    case Never = 'never';

    public function __toString(): string
    {
        return $this->value;
    }
}
