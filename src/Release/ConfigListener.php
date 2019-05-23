<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Phly\KeepAChangelog\Config\ConfigListener as BaseConfigListener;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * Marshal configuration for the ReleaseEvent
 *
 * Overrides the parent constructor to hardcode the flags for requring the
 * package name and remote name.
 */
class ConfigListener extends BaseConfigListener
{
    public function __construct(?EventDispatcherInterface $dispatcher = null)
    {
        parent::__construct(
            $requiresPackageName = true,
            $requiresRemoteName = true,
            $dispatcher
        );
    }
}
