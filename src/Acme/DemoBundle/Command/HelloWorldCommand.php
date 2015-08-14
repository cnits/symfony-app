<?php

namespace Acme\DemoBundle\Command;

use Monolog\Formatter\ElasticaFormatter;
use Monolog\Formatter\LogstashFormatter;
use Monolog\Handler\ElasticSearchHandlerTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ElasticSearchHandler;
use Monolog\Handler\AmqpHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\CouchDBHandler;
use Monolog\Handler\CubeHandler;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\DoctrineCouchDBHandler;
use Monolog\Handler\DynamoDbHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FilterHandler;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\FingersCrossed;//Note
use Monolog\Handler\ZendMonitorHandler;
use Monolog\Handler\WhatFailureGroupHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Handler\SocketHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\NativeMailerHandler;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;

/**
 * Hello World command for demo purposes.
 * You could also extend from Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
 * to get access to the container via $this->getContainer().
 * @author Tobias Schultze <http://tobion.de>
 *
 * https://www.webfactory.de/blog/logging-with-monolog-in-symfony2
 * https://github.com/Seldaek/monolog/blob/master/README.mdown
 *
 * http://symfony.com/doc/current/cookbook/logging/channels_handlers.html
 * http://symfony.com/doc/current/cookbook/logging/monolog.html
 * http://www.whitewashing.de/2012/08/26/symfony__monolog_and_different_log_types.html
 * http://silex.sensiolabs.org/doc/cookbook/multiple_loggers.html
 * https://test-sf-doc-es.readthedocs.org/en/latest/cookbook/logging/monolog.html
 * http://symfony.com/doc/current/reference/configuration/monolog.html
 *
 */
class HelloWorldCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('acme:hello')
            ->setDescription('Hello World example command')
            ->addArgument('who', InputArgument::OPTIONAL, 'Who to greet.', 'World')
            ->setHelp(<<<EOF
                        The <info>%command.name%</info> command greets somebody or everybody:
                        <info>php %command.full_name%</info>
                        The optional argument specifies who to greet:
                        <info>php %command.full_name%</info> Fabien
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $output->writeln(sprintf('Hello <comment>%s</comment>!', $input->getArgument('who')));

        //$this -> processLogMongoDB();
        //$this -> processLogSystem();
        $this -> processLogElasticSearch();
    }

    private function processLogSystem(){
        $sysLogger = new Logger("SysLogger");
        $sysLogger -> pushHandler(new SyslogHandler("sysLogger"));
        $context = array(
            'Context' => '_context'
        );
        $sysLogger -> addInfo("System Log Info {Context}", $context);
    }

    private function processLogMongoDB(){
        $mgdbLogger = new Logger("mongoDBLogger");
        $context = array(
            'context' => "_context"
        );
        $mongoClient = new \Mongo("172.16.4.35:27017");
        $database = "local";
        $collection = "MonoLog";
        $mgdbLogger -> pushHandler(new MongoDBHandler($mongoClient, $database, $collection));
        try{
            $mgdbLogger -> pushProcessor(array(
                'extra' => 'Extra'
            ));
            $mgdbLogger -> log(Logger::EMERGENCY, "Log for Emergency level of {context} ". date("Y-M-d"), $context);
        }catch (\Exception $ex){
            $mgdbLogger -> log(Logger::EMERGENCY, $ex -> getMessage(). date("Y-M-d"), $context);
        }
    }

    private function processLogElasticSearch(){
        $logger = new Logger("elasticSearchLogger");
        $params = array(
            'host' => "172.16.4.35",
            'port' => 9200
        );
        $params['hosts'] = array (
            'http://172.16.4.35:9200',                 // IP + Port
            //'192.168.1.2',                      // Just IP
            //'mydomain.server.com:9201',         // Domain + Port
            //'mydomain2.server.com',             // Just Domain
            //'https://localhost',                // SSL to localhost
            //'https://192.168.1.3:9200',         // SSL to IP + Port
            //'http://user:pass@localhost:9200',  // HTTP Basic Auth
            //'https://user:pass@localhost:9200',  // SSL + HTTP Basic Auth
        );
        try{
            $client = new \Elastica\Client($params);
            $format = new ElasticaFormatter("graylog32", "record");
            $handler = new ElasticSearchHandler($client);
            $handler -> setFormatter($format);
            $logger -> pushHandler($handler);

            $logger -> addInfo("Looger for Elastic-search", array("test"));
        }catch (\Exception $ex){
            var_dump($ex -> getMessage());
        }
    }
}
