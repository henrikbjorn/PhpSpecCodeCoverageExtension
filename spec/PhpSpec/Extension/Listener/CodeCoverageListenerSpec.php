<?php

namespace spec\PhpSpec\Extension\Listener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use PhpSpec\Console\IO;
use PhpSpec\Event\SuiteEvent;

class CodeCoverageListenerSpec extends ObjectBehavior
{
    function let(\PHP_CodeCoverage $coverage)
    {
        $this->beConstructedWith($coverage, array());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PhpSpec\Extension\Listener\CodeCoverageListener');
    }

    function it_should_run_all_reports(
        \PHP_CodeCoverage $coverage,
        \PHP_CodeCoverage_Report_Clover $clover,
        \PHP_CodeCoverage_Report_PHP $php,
        SuiteEvent $event,
        IO $io
    ) {
        $reports = array(
            'clover' => $clover,
            'php' =>  $php
        );

        $this->beConstructedWith($coverage, $reports);
        $this->setOptions(array(
            'format' => array('clover', 'php'),
            'output' => array(
                'clover' => 'coverage.xml',
                'php' => 'coverage.php'
            )
        ));

        $this->setIO($io);

        $clover->process($coverage, 'coverage.xml')->shouldBeCalled();
        $php->process($coverage, 'coverage.php')->shouldBeCalled();

        $this->afterSuite($event);
    }

    function it_should_output_text_report(
        \PHP_CodeCoverage $coverage,
        \PHP_CodeCoverage_Report_Text $text,
        SuiteEvent $event,
        IO $io
    ) {
        $reports = array(
            'text' => $text
        );

        $this->beConstructedWith($coverage, $reports);
        $this->setOptions(array(
            'format' => 'text'
        ));

        $this->setIO($io);
        $io->writeln('')->shouldBeCalled();
        $io->writeln('Generating code coverage report in text format ...')->shouldBeCalled();

        $text->process($coverage, true)->willReturn('report');
        $io->writeln('report')->shouldBeCalled();

        $this->afterSuite($event);
    }
}
