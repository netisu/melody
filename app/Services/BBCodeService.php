<?php

namespace App\Services;

use JBBCode\Parser;
use JBBCode\DefaultCodeDefinitionSet;

class BBCodeService
{
    protected $parser;

    public function __construct()
    {
        $this->parser = new Parser();
        $this->parser->addCodeDefinitionSet(new DefaultCodeDefinitionSet());
    }

    public function parse($bbcode)
    {
        return $this->parser->parse($bbcode)->getAsHTML();
    }
}
