<?php

namespace EXS\SimpleMongoBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 *  Create the nEw collection
 */
class CreateCollectionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('exs:create:collection')
                ->setDescription('Download nats sponsor stats.')
                ->addArgument('collection', InputArgument::REQUIRED, 'collection name?')
                ->addOption('index', null, InputOption::VALUE_OPTIONAL, 'auto index?')
                ->addOption('cap', null, InputOption::VALUE_OPTIONAL, 'cap on size?')
                ->addOption('maxbyte', null, InputOption::VALUE_OPTIONAL, 'max byte?')
                ->addOption('maxdocs', null, InputOption::VALUE_OPTIONAL, 'max number of documents?');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return string
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collectionName = $input->getArgument('collection');
        $index = $input->getOption('index');
        $cap = $input->getOption('cap');
        $maxbyte = $input->getOption('maxbyte');
        $maxdocs = $input->getOption('maxdocs');
        $options = $this->buildOptions($index, $cap, $maxbyte, $maxdocs);        
        
        $service = $this->getContainer()->get('exs_simple_mongo.service');
        $result = $service->createNewCollection($collectionName, $options);
        $output->writeln($result);                
    }
    
    /**
     * Build options for the new collection.
     * 
     * @param boolean $index
     * @param boolean $cap
     * @param int $maxbyte
     * @param int $maxdocs
     * @return \stdClass
     */
    public function buildOptions($index, $cap, $maxbyte, $maxdocs)
    {
        $options = new \stdClass();
        $options->index = $index;
        $options->cap = $cap;
        if($options->cap == 'true') {            
            $options->maxbyte = $maxbyte;
            $options->maxdocs = $maxdocs;
        }
        return $options;
    }
}
