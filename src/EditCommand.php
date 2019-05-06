<?php
/**
 * @see       https://github.com/phly/keep-a-changelog for the canonical source repository
 * @copyright Copyright (c) 2018 Matthew Weier O'Phinney
 * @license   https://github.com/phly/keep-a-changelog/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Phly\KeepAChangelog;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EditCommand extends Command
{
    use GetChangelogFileTrait;

    private const DESCRIPTION = 'Edit the latest changelog entry using the system editor.';

    private const HELP = <<< 'EOH'
Edit the latest changelog entry using the system editor ($EDITOR), or the
editor provided via --editor.

By default, the command will edit CHANGELOG.md in the current directory, unless
a different file is specified via the --file option.
EOH;

    protected function configure() : void
    {
        $this->setDescription(self::DESCRIPTION);
        $this->setHelp(self::HELP);
        $this->addArgument(
            'version',
            InputArgument::OPTIONAL,
            'A specific changelog version to edit.'
        );
        $this->addOption(
            'editor',
            '-e',
            InputOption::VALUE_REQUIRED,
            'Alternate editor command to use to edit the changelog.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $editor        = $input->getOption('editor') ?: null;
        $version       = $input->getArgument('version') ?: null;
        $changelogFile = $this->getChangelogFile($input);

        if (! (new Edit())($output, $changelogFile, $editor, $version)) {
            $output->writeln(sprintf(
                '<error>Could not edit %s; please check the output for details.</error>',
                $changelogFile
            ));
            return 1;
        }

        $message = $version
            ? sprintf('<info>Edited change for version %s in %s</info>', $version, $changelogFile)
            : sprintf('<info>Edited most recent changelog in %s</info>', $changelogFile);
        $output->writeln($message);

        return 0;
    }
}
