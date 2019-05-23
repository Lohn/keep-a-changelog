<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Provider;

class ProviderList
{
    /**
     * @var array<string, ProviderInterface>
     */
    private $providers = [];

    public function has(string $name) : bool
    {
        return isset($this->providers[$name]);
    }

    public function get(string $name) : ?ProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    public function set(string $name, ProviderInterface $provider) : void
    {
        $this->providers[$name] = $provider;
    }

    public function listKnownTypes() : array
    {
        return array_keys($this->providers);
    }
}
