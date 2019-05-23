<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Release;

use Throwable;

class CreateReleaseListener
{
    public function __invoke(CreateReleaseEvent $event) : void
    {
        $releaseName = $event->releaseName();
        $provider    = $event->provider();

        $event->output()->writeln(sprintf(
            '<info>Creating release "%s"</info>',
            $releaseName
        ));

        try {
            $release = $provider->createRelease(
                $event->package(),
                $releaseName,
                $event->version(),
                $event->changelog(),
                $event->token()
            );
        } catch (Throwable $e) {
            $event->errorCreatingRelease($e);
            return;
        }

        if (! $release) {
            $event->unexpectedProviderResult();
            return;
        }

        $event->releaseCreated($release);
    }
}
