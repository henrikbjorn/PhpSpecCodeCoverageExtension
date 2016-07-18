<?php

namespace PhpSpec\Extension;

// PhpSpec changed the name of this interface
if (!interface_exists('PhpSpec\\ExtensionInterface')) {
    interface ExtensionInterface extends Extension {
    }
}
