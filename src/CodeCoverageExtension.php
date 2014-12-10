<?php

namespace PhpSpec\Extension;

use PhpSpec\ServiceContainer;
use PhpSpec\Extension\Listener\CodeCoverageListener;

/**
 * Injects a Event Subscriber into the EventDispatcher. The Subscriber
 * will before each example add CodeCoverage Information.
 */
class CodeCoverageExtension implements \PhpSpec\Extension\ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ServiceContainer $container)
    {
        $container->setShared('code_coverage.filter', function () {
            return new \PHP_CodeCoverage_Filter();
        });

        $container->setShared('code_coverage', function ($container) {
            return new \PHP_CodeCoverage(null, $container->get('code_coverage.filter'));
        });

        $container->setShared('code_coverage.options', function ($container) {
            $options = $container->getParam('code_coverage');

            if (!isset($options['format'])) {
                $options['format'] = array('html');
            } elseif (!is_array($options['format'])) {
                $options['format'] = (array) $options['format'];
            }

            if (isset($options['output'])) {
                if (!is_array($options['output']) && count($options['format']) == 1) {
                    $format = $options['format'][0];
                    $options['output'] = array($format => $options['output']);
                }
            }

            if (!isset($options['show_uncovered_files'])) {
                $options['show_uncovered_files'] = true;
            }
            if (!isset($options['lower_upper_bound'])) {
                $options['lower_upper_bound'] = 35;
            }
            if (!isset($options['high_lower_bound'])) {
                $options['high_lower_bound'] = 70;
            }

            return $options;
        });

        $container->setShared('code_coverage.reports', function ($container) {
            $options = $container->get('code_coverage.options');

            $reports = array();
            foreach ($options['format'] as $format) {
                switch ($format) {
                    case 'clover':
                        $reports['clover'] = new \PHP_CodeCoverage_Report_Clover();
                        break;
                    case 'php':
                        $reports['php'] =  new \PHP_CodeCoverage_Report_PHP();
                        break;
                    case 'text':
                        $reports['text'] =  new \PHP_CodeCoverage_Report_Text(
                            $options['lower_upper_bound'],
                            $options['high_lower_bound'],
                            $options['show_uncovered_files'],
                            /* $showOnlySummary */ false
                        );
                        break;
                    case 'xml':
                        $reports['xml'] =  new \PHP_CodeCoverage_Report_XML();
                        break;
                    case 'crap4j':
                        $reports['crap4j'] = new \PHP_CodeCoverage_Report_Crap4j();
                        break;
                    case 'html':
                        $reports['html'] = new \PHP_CodeCoverage_Report_HTML();
                        break;
                }
            }

            $container->setParam('code_coverage', $options);
            return $reports;
        });

        $container->setShared('event_dispatcher.listeners.code_coverage', function ($container) {
            $listener = new CodeCoverageListener(
                $container->get('code_coverage'),
                $container->get('code_coverage.reports')
            );
            $listener->setIO($container->get('console.io'));
            $listener->setOptions($container->getParam('code_coverage', array()));

            return $listener;
        });
    }
}
