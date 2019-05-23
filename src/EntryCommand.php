<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Phly\KeepAChangelog\Provider\ProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function explode;
use function in_array;
use function ucwords;

class EntryCommand extends Command
{
    use Config\CommonConfigOptionsTrait;

    private const DESC_TEMPLATE = 'Create a new changelog entry for the latest changelog in the "%s" section';

    private const HELP_TEMPLATE = <<<'EOH'
In the latest changelog entry, add the given entry in the section marked
"%s".

If the first entry in that section matches "- Nothing", that line will
be replaced with the new entry.

When the --pr option is provided, the entry will be prepended with a link
to the given pull request. If no --package option is present, we will
attempt to determine the package name from the composer.json file.
EOH;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var string */
    private $type;

    /**
     * @throws Exception\InvalidNoteTypeException
     */
    public function __construct(EventDispatcherInterface $dispatcher, string $name)
    {
        $this->dispatcher = $dispatcher;

        if (false === strpos($name, ':')) {
            throw Exception\InvalidNoteTypeException::forCommandName($name);
        }

        [$initial, $type] = explode(':', $name, 2);
        if (! in_array($type, Entry\EntryTypes::TYPES, true)) {
            throw Exception\InvalidNoteTypeException::forCommandName($name);
        }

        $this->type = $type;
        parent::__construct($name);
    }

    protected function configure() : void
    {
        $this->setDescription(sprintf(
            self::DESC_TEMPLATE,
            ucwords($this->type)
        ));
        $this->setHelp(sprintf(
            self::HELP_TEMPLATE,
            ucwords($this->type)
        ));
        $this->addArgument(
            'entry',
            InputArgument::REQUIRED,
            'Entry to add to the changelog'
        );
        $this->addOption(
            'pr',
            null,
            InputOption::VALUE_REQUIRED,
            'Pull request number to associate with entry'
        );

        $this->injectPackageOption($this);
        $this->injectProviderOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        return $this->dispatcher
            ->dispatch(new Entry\AddChangelogEntryEvent(
                $input,
                $output,
                $this->type,
                $input->getArgument('entry'),
                $input->getOption('pr')
            ))
            ->failed()
            ? 1
            : 0;
    }
}
