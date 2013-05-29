<?php

namespace PhpSpec\Extension\Listener;

use PhpSpec\Console\IO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;

class CodeCoverageListener implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    private $coverage;
    private $report;
    private $io;
    private $options;

    public function __construct(\PHP_CodeCoverage $coverage, \PHP_CodeCoverage_Report_HTML $report)
    {
        $this->coverage = $coverage;
        $this->report   = $report;
        $this->options  = array(
            'whitelist' => array('src', 'lib'),
            'blacklist' => array('vendor', 'spec'),
            'output'    => 'coverage',
        );
    }

    public function beforeSuite(SuiteEvent $event)
    {
        $filter = $this->coverage->filter();

        array_map(array($filter, 'addDirectoryToWhitelist'), $this->options['whitelist']);
        array_map(array($filter, 'addDirectoryToBlacklist'), $this->options['blacklist']);
    }

    public function beforeExample(ExampleEvent $event)
    {
        $example = $event->getExample();

        $name = strtr('%spec%::%example%', array(
            '%spec%' => $example->getSpecification()->getClassReflection()->getName(),
            '%example%' => $example->getFunctionReflection()->getName(),
        ));

        $this->coverage->start($name);
    }

    public function afterExample(ExampleEvent $event)
    {
        $this->coverage->stop();
    }

    public function afterSuite(SuiteEvent $event)
    {
        if ($this->io) {
            $this->io->writeln('');
            $this->io->writeln('Generating code coverage report in HTML format ...');
        }

        $this->report->process($this->coverage, $this->options['output']);
    }

    public function setIO(IO $io)
    {
        $this->io = $io;
    }

    public function setOptions(array $options)
    {
        $this->options = $this->options + $options;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'beforeExample' => array('beforeExample', -10),
            'afterExample'  => array('afterExample', -10),
            'beforeSuite'   => array('beforeSuite', -10),
            'afterSuite'    => array('afterSuite', -10),
        );
    }
}
