<?php

declare(strict_types=1);

use Doctrine\Deprecations\Deprecation;

require_once dirname(__DIR__) . '/vendor/autoload.php';

Deprecation::enableWithTriggerError();
Deprecation::withoutDeduplication();
