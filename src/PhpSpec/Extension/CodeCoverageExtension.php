<?php

namespace PhpSpec\Extension;

use PhpSpec\ServiceContainer;

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

        $container->setShared('code_coverage.report', function () {
            return new \PHP_CodeCoverage_Report_HTML;
        });

        $container->setShared('event_dispatcher.listeners.code_coverage', function ($container) {
            $options = (array) $container->getParam('code_coverage');

            return new Listener\CodeCoverageListener(
                $container->get('code_coverage'),
                $container->get('code_coverage.report'),
                $options
            );
        });
    }
}
