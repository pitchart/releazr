<?php

namespace Pitchart\Releazr\Command;

use Gitonomy\Git\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ListCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function configure()
    {
        $this->setName('releases')
            ->setDescription('Lists releases from a github project')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path to the local git repository')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repositoryPath = $input->getOption('path') ? realpath($input->getOption('path')) : getcwd();

        $repositoryPath = realpath($repositoryPath);

        $output->writeln($repositoryPath);

        $repository = new Repository($repositoryPath);

        $tags = new \Pitchart\Collection\Collection($repository->getReferences()->getTags());

        $versions = $tags->filter(function ($tag) {
            return preg_match('#refs/tags/v?\d.\d.\d.*#', $tag->getFullname());
        });

        $tags->each(function ($version) use ($output) {
            $output->writeln($version->getFullname());
        });

        $versions->each(function ($version) use ($output) {
            $output->writeln($version->getFullname());
        });

        //var_dump($repository->getReferences()->getTags());
    }
}
