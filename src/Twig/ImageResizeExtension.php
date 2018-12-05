<?php

namespace App\Twig;

use Symfony\Component\HttpFoundation\File\File;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ImageResizeExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('imgResize', [$this, 'imageResize'])
        ];
    }

    public function imageResize(){

    }

}