<?php

return (new PhpCsFixer\Config())

    ->setIndent("\t")
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        'yoda_style' => ['equal' => false, 'identical' => false],
    ])
    ->setFinder(PhpCsFixer\Finder::create()
                ->exclude('vendor')
                ->exclude('config')
                ->in(__DIR__)
    )
    ;
