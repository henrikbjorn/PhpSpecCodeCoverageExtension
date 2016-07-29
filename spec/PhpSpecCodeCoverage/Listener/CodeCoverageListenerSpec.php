<?php

namespace spec\PhpSpecCodeCoverage\Listener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use PhpSpec\Console\ConsoleIO;
use PhpSpec\Event\SuiteEvent;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report;

class CodeCoverageListenerSpec extends ObjectBehavior
{
    function let(ConsoleIO $io, CodeCoverage $coverage)
    {
        $this->beConstructedWith($io, $coverage, array());
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\PhpSpecCodeCoverage\Listener\CodeCoverageListener');
    }

    function it_should_run_all_reports(
        CodeCoverage $coverage,
        Report\Clover $clover,
        Report\PHP $php,
        SuiteEvent $event,
        ConsoleIO $io
    ) {
        $reports = array(
            'clover' => $clover,
            'php' =>  $php
        );

        $this->beConstructedWith($io, $coverage, $reports);
        $this->setOptions(array(
            'format' => array('clover', 'php'),
            'output' => array(
                'clover' => 'coverage.xml',
                'php' => 'coverage.php'
            )
        ));

        $clover->process($coverage, 'coverage.xml')->shouldBeCalled();
        $php->process($coverage, 'coverage.php')->shouldBeCalled();

        $this->afterSuite($event);
    }

    function it_should_color_output_text_report_by_default(
        CodeCoverage $coverage,
        Report\Text $text,
        SuiteEvent $event,
        ConsoleIO $io
    ) {
        $reports = array(
            'text' => $text
        );

        $this->beConstructedWith($io, $coverage, $reports);
        $this->setOptions(array(
            'format' => 'text'
        ));

        $io->isVerbose()->willReturn(false);
        $io->isDecorated()->willReturn(true);

        $text->process($coverage, true)->willReturn('report');
        $io->writeln('report')->shouldBeCalled();

        $this->afterSuite($event);
    }

    function it_should_not_color_output_text_report_unless_specified(
        CodeCoverage $coverage,
        Report\Text $text,
        SuiteEvent $event,
        ConsoleIO $io
    ) {
        $reports = array(
            'text' => $text
        );

        $this->beConstructedWith($io, $coverage, $reports);
        $this->setOptions(array(
            'format' => 'text'
        ));

        $io->isVerbose()->willReturn(false);
        $io->isDecorated()->willReturn(false);

        $text->process($coverage, false)->willReturn('report');
        $io->writeln('report')->shouldBeCalled();

        $this->afterSuite($event);
    }

    function it_should_output_html_report(
        CodeCoverage $coverage,
        Report\Html\Facade $html,
        SuiteEvent $event,
        ConsoleIO $io
    ) {
        $reports = array(
            'html' => $html
        );

        $this->beConstructedWith($io, $coverage, $reports);
        $this->setOptions(array(
            'format' => 'html',
            'output' => array('html' => 'coverage'),
        ));

        $io->isVerbose()->willReturn(false);
        $io->writeln(Argument::any())->shouldNotBeCalled();


        $html->process($coverage, 'coverage')->willReturn('report');

        $this->afterSuite($event);
    }

    function it_should_provide_extra_output_in_verbose_mode(
        CodeCoverage $coverage,
        Report\Html\Facade $html,
        SuiteEvent $event,
        ConsoleIO $io
    ) {
        $reports = array(
            'html' => $html,
        );

        $this->beConstructedWith($io, $coverage, $reports);
        $this->setOptions(array(
            'format' => 'html',
            'output' => array('html' => 'coverage'),
        ));

        $io->isVerbose()->willReturn(true);
        $io->writeln('')->shouldBeCalled();
        $io->writeln('Generating code coverage report in html format ...')->shouldBeCalled();

        $this->afterSuite($event);
    }

    function it_should_correctly_handle_black_listed_files_and_directories(
        CodeCoverage $coverage,
        SuiteEvent $event,
        Filter $filter,
        ConsoleIO $io
    )
    {
        $this->beConstructedWith($io, $coverage, array());

        $coverage->filter()->willReturn($filter);

        $this->setOptions(array(
            'whitelist' => array('src'),
            'blacklist' => array('src/filter'),
            'whitelist_files' => array('src/filter/whilelisted_file'),
            'blacklist_files' => array('src/filtered_file')
        ));

        $filter->addDirectoryToWhitelist('src')->shouldBeCalled();
        $filter->removeDirectoryFromWhitelist('src/filter')->shouldBeCalled();
        $filter->addFileToWhitelist('src/filter/whilelisted_file')->shouldBeCalled();
        $filter->removeFileFromWhitelist('src/filtered_file')->shouldBeCalled();

        $this->beforeSuite($event);
    }
}
