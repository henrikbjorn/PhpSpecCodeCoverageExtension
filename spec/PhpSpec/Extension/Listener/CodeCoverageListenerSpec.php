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

        $io->isVerbose()->willReturn(false);
        $this->setIO($io);

        $text->process($coverage, true)->willReturn('report');
        $io->writeln('report')->shouldBeCalled();

        $this->afterSuite($event);
    }

    function it_should_output_html_report(
        \PHP_CodeCoverage $coverage,
        \PHP_CodeCoverage_Report_HTML $html,
        SuiteEvent $event,
        IO $io
    ) {
        $reports = array(
            'html' => $html
        );

        $this->beConstructedWith($coverage, $reports);
        $this->setOptions(array(
            'format' => 'html',
            'output' => array('html' => 'coverage'),
        ));

        $io->isVerbose()->willReturn(false);
        $this->setIO($io);

        $io->writeln(Argument::any())->shouldNotBeCalled();


        $html->process($coverage, 'coverage')->willReturn('report');

        $this->afterSuite($event);
    }

    function it_should_provide_extra_output_in_verbose_mode(
        \PHP_CodeCoverage $coverage,
        \PHP_CodeCoverage_Report_HTML $html,
        SuiteEvent $event,
        IO $io
    ) {
        $reports = array(
            'html' => $html,
        );

        $this->beConstructedWith($coverage, $reports);
        $this->setOptions(array(
            'format' => 'html',
            'output' => array('html' => 'coverage'),
        ));

        $io->isVerbose()->willReturn(true);
        $this->setIO($io);

        $io->writeln('')->shouldBeCalled();
        $io->writeln('Generating code coverage report in html format ...')->shouldBeCalled();

        $this->afterSuite($event);
    }

    function it_should_correctly_handle_black_listed_files_and_directories(
        \PHP_CodeCoverage $coverage,
        SuiteEvent $event,
        \PHP_CodeCoverage_Filter $filter
    )
    {
        $this->beConstructedWith($coverage, array());

        $coverage->filter()->willReturn($filter);

        $this->setOptions(array(
            'whitelist' => array('src'),
            'blacklist' => array('src/filter'),
            'whitelist_files' => array('src/filter/whilelisted_file'),
            'blacklist_files' => array('src/filtered_file')
        ));

        $filter->addDirectoryToWhitelist('src')->willReturn(null);
        $filter->removeDirectoryFromWhitelist('src/filter')->willReturn(null);
        $filter->addDirectoryToBlacklist('src/filter')->willReturn(null);
        $filter->addFileToWhitelist('src/filter/whilelisted_file')->willReturn(null);
        $filter->removeFileFromWhitelist('src/filtered_file')->willReturn(null);
        $filter->addFileToBlacklist('src/filtered_file')->willReturn(null);

        $this->beforeSuite($event);
    }
}
