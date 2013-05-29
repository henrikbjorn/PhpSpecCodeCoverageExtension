<?php

namespace PhpSpec\Extension\Listener;

use PhpSpec\Console\IO;
use PhpSpec\Event\SpecificationEvent;
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

    public function beforeSpecification(SpecificationEvent $event)
    {
        $this->coverage->start($event->getSpecification()->getTitle());
    }

    public function afterSpecification(SpecificationEvent $event)
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
            'beforeSpecification' => array('beforeSpecification', -10),
            'afterSpecification' => array('afterSpecification', -10),
            'beforeSuite' => array('beforeSuite', -10),
            'afterSuite' => array('afterSuite', -10),
        );
    }
}
