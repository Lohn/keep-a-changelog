<?php

/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Milestone;

use Phly\KeepAChangelog\Provider\MilestoneAwareProviderInterface;

class VerifyProviderIsMilestoneAwareListener
{
    public function __invoke(AbstractMilestoneProviderEvent $event) : void
    {
        $provider = $event->provider();

        if (! $provider instanceof MilestoneAwareProviderInterface) {
            $event->providerIncapableOfMilestones();
        }
    }
}
