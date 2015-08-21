<?php
/*
 * Code Owner: Cnit*
 * Modified Date: 8/21/2015
 * Modified By: Phong Lam
 */
namespace CnitLoggerBundle\Command;
use CnitLoggerBundle\Service\LoggerManager;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GrayLogCommand extends Command {
    protected function configure()
    {
        $this
            ->setName('cnit:gray-log2')
            ->setDescription('Description info')
            ->setHelp("Help")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        LoggerManager::executeGrayLogService(LogLevel::ERROR, "I come from a strange site!", array(), "PVLam Test");
    }
}