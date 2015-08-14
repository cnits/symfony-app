<?php

namespace Acme\DemoBundle\Command;

use Gelf\Logger;
use Gelf\Message;
use Gelf\Publisher;
use Gelf\Transport\UdpTransport;
use Monolog\Formatter\ElasticaFormatter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Hello World command for demo purposes.
 * You could also extend from Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
 * to get access to the container via $this->getContainer().
 * @author Tobias Schultze <http://tobion.de>
 *
 */
class GrayLogCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('acme:graylog')
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
     * {@inh
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        try{
            $transport = new UdpTransport("172.16.4.36");
            $publisher = new Publisher();
            $publisher -> addTransport($transport);
            $facility = "I don't know what facility is. Please, show me know what was it used to do?";
            $exception = new \Exception("This is an exception");

            $logger = new Logger($publisher, $facility);
            $logger -> alert("Set an Alert level for Graylog2", array('user' => 'User-Name'));
            $logger -> critical("Set a Critical level for Graylog2", array('_user' => "User Name", 'exception' => $exception));
            $logger -> emergency("Set an Emergency level for Graylog2", array('_user' => "User Name"));
            $logger -> error("Set an Error level for Graylog2", array('_user' => "User Name"));
            $logger -> info("Set an Info level for Graylog2", array('_user' => "User Name"));
            $logger -> debug("Set a Debug level for Graylog2", array('_user' => "User Name"));
            $logger -> warning("Set a Warning level for Graylog2", array('_user' => "User Name"));
            $logger -> notice("Set a Notice level for Graylog2", array('_user' => "User Name"));

            /*$message = new Message();
            $message -> setFullMessage("GrayLog2 with full messages")
                     -> setShortMessage("GrayLog2 with short message")
                     -> setLevel(6);
            $publisher -> publish($message);*/
            echo "The end!";
        }catch(\Exception $ex){
            var_dump($ex -> getMessage());
        }
    }
}
