<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog\Config;

use RuntimeException;

use function rtrim;
use function sprintf;
use function strtr;

/**
 * Parses the global config to populate the Config instance.
 *
 * The global configuration file is an INI file with the following format:
 *
 * <code>
 * [defaults]
 * changelog_file = changelog.md
 * provider = custom
 * remote = upstream
 *
 * [providers]
 * github[class] = Phly\KeepAChangelog\Provider\GitHub
 * github[url] = https://github.mwop.net
 * github[token] = this-is-a-token
 * custom[class] = Mwop\Git\Provider
 * custom[url] = https://git.mwop.net
 * custom[token] = this-is-a-token
 * gitlab[class] = Phly\KeepAChangelog\Provider\GitHub
 * gitlab[token] = this-is-a-token
 * </code>
 */
class RetrieveGlobalConfigListener extends AbstractConfigListener
{
    /**
     * Set the global config root directory.
     *
     * For testing purposes only. Use this to set the config root to use
     * when attempting to find the config file.
     *
     * @internal
     * @var null|string
     */
    public $configRoot;

    protected function getConfigFile() : string
    {
        return sprintf('%s/keep-a-changelog.ini', $this->getConfigRoot());
    }

    private function getConfigRoot() : string
    {
        if ($this->configRoot) {
            return $this->configRoot;
        }

        $configRoot = getenv('XDG_CONFIG_HOME');
        if ($configRoot) {
            return $this->normalizePath($configRoot);
        }

        $configRoot = getenv('HOME');
        if (! $configRoot) {
            throw new RuntimeException(
                'keep-a-changelog requires either the XDG_CONFIG_HOME or HOME'
                . ' env variables be set in order to operate.'
            );
        }

        return sprintf('%s/.config', $this->normalizePath($configRoot));
    }

    private function normalizePath(string $path) : string
    {
        return rtrim(strtr($path, '\\', '/'), '/');
    }
}
