<?php

namespace App\Twig;

use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageResizeExtension extends AbstractExtension
{
    private $fileSystem;
    private $imageDir;

    public function __construct(Filesystem $fileSystem, $imageDir)
    {
        $this->fileSystem = $fileSystem;
        $this->imageDir = $imageDir;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction(
                'imgResize',
                [
                    $this,
                    'imageResize'
                ]
            )
        ];
    }

    public function imageResize(string $imageSrc, int $width, int $height)
    {
        $imagePath = $this->imageDir . $imageSrc;
        if(!$this->fileSystem->exists($imagePath)) {
            $imagePath = "/images/book.jpeg";
        }

        echo "
                    <div class=\"img-container\" style=\"width: " . $width . "px; height: " . $height . "px\">
                        <img src='". "/" . $imagePath . "'>
                    </div>
                 "
        ;
    }
}