<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlyTest\KeepAChangelog;

use Phly\KeepAChangelog\Config;
use Phly\KeepAChangelog\Exception;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConstructorRaisesExceptionForInvalidProvider()
    {
        $this->expectException(Exception\InvalidProviderException::class);
        new Config('', 'unknown-provider-name');
    }

    public function testConstructorAllowsPassingNoArguments()
    {
        $config = new Config();
        $this->assertSame('', $config->token());
        $this->assertSame(Config::PROVIDER_GITHUB, $config->provider());
    }

    public function testConstructorAllowsProvidingProviderArgument()
    {
        $config = new Config('token-value', Config::PROVIDER_GITLAB);
        $this->assertSame('token-value', $config->token());
        $this->assertSame(Config::PROVIDER_GITLAB, $config->provider());
        $this->assertSame('gitlab.com', $config->domain());
    }

    public function testConstructorAllowsProvidingProviderAndProviderDomainArguments() : Config
    {
        $config = new Config('token-value', Config::PROVIDER_GITLAB, 'gitlab.phly.dev');
        $this->assertSame('token-value', $config->token());
        $this->assertSame(Config::PROVIDER_GITLAB, $config->provider());
        $this->assertSame('gitlab.phly.dev', $config->domain());
        return $config;
    }

    /**
     * @depends testConstructorAllowsProvidingProviderAndProviderDomainArguments
     */
    public function testGetArrayCopyProvidesSerialization(Config $config)
    {
        $this->assertSame([
            'token' => $config->token(),
            'provider' => $config->provider(),
            'domain' => $config->domain(),
        ], $config->getArrayCopy());
    }

    public function testWithDomainReturnsNewInstanceWithChangedDomain()
    {
        $config = new Config();
        $changed = $config->withDomain('gitlab.phly.dev');
        $this->assertNotSame($changed, $config);
        $this->assertSame('gitlab.phly.dev', $changed->domain());
    }

    public function testWithTokenReturnsNewInstanceWithChangedToken()
    {
        $config = new Config();
        $changed = $config->withToken('new-token-value');
        $this->assertNotSame($changed, $config);
        $this->assertSame('new-token-value', $changed->token());
    }

    public function testWithProviderReturnsNewInstanceWithChangedProvider()
    {
        $config = new Config();
        $changed = $config->withProvider(Config::PROVIDER_GITLAB);
        $this->assertNotSame($changed, $config);
        $this->assertSame(Config::PROVIDER_GITLAB, $changed->provider());
    }

    public function testWithProviderRaisesExceptionForUnknownProviderType()
    {
        $config = new Config();
        $this->expectException(Exception\InvalidProviderException::class);
        $config->withProvider('unknown-provider-type');
    }
}
