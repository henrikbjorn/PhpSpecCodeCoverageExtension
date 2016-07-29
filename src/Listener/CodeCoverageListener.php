<?php

namespace HenrikBjorn\PhpSpecCodeCoverage\Listener;

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\ExampleEvent;
use PhpSpec\Event\SuiteEvent;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CodeCoverageListener implements EventSubscriberInterface
{
    private $coverage;
    private $reports;
    private $io;
    private $options;
    private $enabled;

    public function __construct(ConsoleIO $io, CodeCoverage $coverage, array $reports)
    {
	$this->io = $io;
        $this->coverage = $coverage;
        $this->reports  = $reports;
        $this->options  = array(
            'whitelist' => array('src', 'lib'),
            'blacklist' => array('test', 'vendor', 'spec'),
            'whitelist_files' => array(),
            'blacklist_files' => array(),
            'output'    => array('html' => 'coverage'),
            'format'    => array('html'),
        );

        $this->enabled = extension_loaded('xdebug') || (PHP_SAPI === 'phpdbg');
    }

    public function beforeSuite(SuiteEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $filter = $this->coverage->filter();

        array_walk(
            $this->options['whitelist'],
            [$filter, 'addDirectoryToWhitelist']
        );
        array_walk(
            $this->options['blacklist'],
            [$filter, 'removeDirectoryFromWhitelist']
        );

        array_walk(
            $this->options['whitelist_files'],
            [$filter, 'addFileToWhitelist']
        );

        array_walk(
            $this->options['blacklist_files'],
            [$filter, 'removeFileFromWhitelist']
        );
    }

    public function beforeExample(ExampleEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $example = $event->getExample();

        $name = strtr('%spec%::%example%', array(
            '%spec%' => $example->getSpecification()->getClassReflection()->getName(),
            '%example%' => $example->getFunctionReflection()->getName(),
        ));

        $this->coverage->start($name);
    }

    public function afterExample(ExampleEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $this->coverage->stop();
    }

    public function afterSuite(SuiteEvent $event)
    {
        if (!$this->enabled) {
            if ($this->io && $this->io->isVerbose()) {
                $this->io->writeln('Did not detect Xdebug extension or phpdbg. No code coverage will be generated.');
            }

            return;
        }

        if ($this->io && $this->io->isVerbose()) {
            $this->io->writeln('');
        }

        foreach ($this->reports as $format => $report) {
            if ($this->io && $this->io->isVerbose()) {
                $this->io->writeln(sprintf('Generating code coverage report in %s format ...', $format));
            }

            if ($report instanceof Report\Text) {
                $output = $report->process($this->coverage, /* showColors */ $this->io->isDecorated());
                $this->io->writeln($output);
            } else {
                $report->process($this->coverage, $this->options['output'][$format]);
            }
        }
    }

    public function setOptions(array $options)
    {
        $this->options = $options + $this->options;
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
