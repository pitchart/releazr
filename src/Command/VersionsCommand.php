<?php

namespace Pitchart\Releazr\Command;

use Composer\Semver\Comparator;
use Composer\Semver\Semver;
use Gitonomy\Git\Reference;
use Gitonomy\Git\Repository;
use Pitchart\Version\Version;
use Pitchart\Version\VersionException;
use Pitchart\Collection\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class VersionsCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this->setName('versions')
            ->setDescription('Lists version tags from a github project')
            ->addArgument('path', null, InputArgument::OPTIONAL, 'Path to the local git repository', getcwd())
            ->addOption('last', null, InputOption::VALUE_NONE, 'Only display the latest version')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repositoryPath = realpath($input->getArgument('path'));

        $repository = new Repository($repositoryPath);
        $tags = new Collection($repository->getReferences()->getTags());

        /** @var Collection $versions */
        $versions = $tags->reduce(function ($accumulator, Reference $tag) {
            if (preg_match('#refs/tags/v?(?P<version>\d.\d.\d.*)#', $tag->getFullname(), $matches)) {
                try {
                    $version = new Version($matches['version']);
                    $accumulator[] = $version;
                }
                catch (VersionException $e) {
                }
            }
            return $accumulator;
        }, new Collection);

        if ($input->getOption('last')) {
            $version = $versions->reduce(function ($accumulator, Version $version) {
                if (Comparator::greaterThanOrEqualTo($version, $accumulator)) {
                    $accumulator = $version;
                }
                return $accumulator;
            }, null);
            $output->writeln((string) $version);
        }
        else {
            $versions->each(function (Version $version) use ($output) {
                $output->writeln($version->__toString());
            });
        }

    }
}
