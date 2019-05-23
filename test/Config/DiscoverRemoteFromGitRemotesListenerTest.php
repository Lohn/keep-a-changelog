<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Config\DiscoverRemoteFromGitRemotesListener;
use Phly\KeepAChangelog\Config\RemoteNameDiscovery;
use Phly\KeepAChangelog\Provider\ProviderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class DiscoverRemoteFromGitRemotesListenerTest extends TestCase
{
    public function setUp()
    {
        $this->provider = $this->prophesize(ProviderInterface::class);
        $this->config = $this->prophesize(Config::class);
        $this->config->provider()->will([$this->provider, 'reveal']);
        $this->event = $this->prophesize(RemoteNameDiscovery::class);
        $this->event->config()->will([$this->config, 'reveal']);
    }

    public function testReturnsEarlyIfEventIndicatesRemoteWasAlreadyFound()
    {
        $this->event->remoteWasFound()->willReturn(true);

        $listener = new DiscoverRemoteFromGitRemotesListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldNotHaveBeenCalled();
        $this->event->reportNoMatchingGitRemoteFound(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->foundRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldNotHaveBeenCalled();
        $this->provider->domain()->shouldNotHaveBeenCalled();
    }

    public function testReturnsEarlyIfProviderHasNoDomain()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->provider->domain()->willReturn('');

        $listener = new DiscoverRemoteFromGitRemotesListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldHaveBeenCalled();
        $this->event->reportNoMatchingGitRemoteFound(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->foundRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldHaveBeenCalled();
    }

    public function testReturnsEarlyIfConfigHasNoPackageAssociated()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->provider->domain()->willReturn('git.mwop.net');
        $this->config->package()->willReturn(null);

        $listener = new DiscoverRemoteFromGitRemotesListener();

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldHaveBeenCalled();
        $this->event->reportNoMatchingGitRemoteFound(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->foundRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldHaveBeenCalled();
    }

    public function testReportsNoMatchingGitRemotesFoundIfCommandFails()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->provider->domain()->willReturn('git.mwop.net');
        $this->config->package()->willReturn('some/package');
        $this->event
            ->reportNoMatchingGitRemoteFound(
                'git.mwop.net',
                'some/package'
            )
            ->shouldBeCalled();

        $listener = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $return = 1;
        };

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldHaveBeenCalled();
        $this->event->foundRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldHaveBeenCalled();
    }

    public function testReportsNoMatchingGitRemotesFoundIfNoRemotesMatchDomainAndPackageCombination()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->provider->domain()->willReturn('git.mwop.net');
        $this->config->package()->willReturn('some/package');
        $this->event
            ->reportNoMatchingGitRemoteFound(
                'git.mwop.net',
                'some/package'
            )
            ->shouldBeCalled();

        $listener = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $output = [
                'origin https://github.com/some/package.git (push)',
                'upstream me@gitlab.com:some/package.git (push)',
                'myself git://git.mwop.net/another/package.git (push)',
                'readonly git://git.mwop.net/some/package.git (pull)',
            ];
        };

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldHaveBeenCalled();
        $this->event->foundRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldHaveBeenCalled();
    }

    public function testReportsRemoteFoundIfExactlyOneRemoteMatches()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->provider->domain()->willReturn('git.mwop.net');
        $this->config->package()->willReturn('some/package');
        $this->event
            ->foundRemote('myself')
            ->shouldBeCalled();

        $listener = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $output = [
                'origin https://github.com/some/package.git (push)',
                'upstream me@gitlab.com:some/package.git (push)',
                'myself git://git.mwop.net/some/package.git (push)',
            ];
        };

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldHaveBeenCalled();
        $this->event->reportNoMatchingGitRemoteFound(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->setRemotes(Argument::any())->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldHaveBeenCalled();
    }

    public function testReportsMultipleRemotesFoundIfMoreThanOneRemoteMatches()
    {
        $this->event->remoteWasFound()->willReturn(false);
        $this->provider->domain()->willReturn('git.mwop.net');
        $this->config->package()->willReturn('some/package');
        $this->event
            ->setRemotes(['upstream', 'myself'])
            ->shouldBeCalled();

        $listener = new DiscoverRemoteFromGitRemotesListener();
        $listener->exec = function (string $command, array &$output, int &$return) {
            $output = [
                'origin https://github.com/some/package.git (push)',
                'upstream me@git.mwop.net:some/package.git (push)',
                'myself git://git.mwop.net/some/package.git (push)',
            ];
        };

        $this->assertNull($listener($this->event->reveal()));

        $this->event->config()->shouldHaveBeenCalled();
        $this->event->reportNoMatchingGitRemoteFound(Argument::any())->shouldNotHaveBeenCalled();
        $this->event->foundRemote(Argument::any())->shouldNotHaveBeenCalled();
        $this->config->provider()->shouldHaveBeenCalled();
    }
}
