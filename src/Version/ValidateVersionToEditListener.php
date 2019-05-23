<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Version;

use Phly\KeepAChangelog\Common\ValidateVersionListener;
use Phly\KeepAChangelog\Common\VersionAwareEventInterface;

class ValidateVersionToEditListener extends ValidateVersionListener
{
    public function __invoke(VersionAwareEventInterface $event) : void
    {
        if (! $event->version()) {
            // null is a valid version for this workflow; equates to "most recent"
            return;
        }

        parent::__invoke($event);
    }
}
