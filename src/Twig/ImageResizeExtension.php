<?php

namespace App\Twig;

use Intervention\Image\ImageManager;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageResizeExtension extends AbstractExtension
{
    private $fileSystem;
    private $imageResizeDir;
    private $imageDir;

    public function __construct(Filesystem $fileSystem, $imageDir, $imageResizeDir)
    {
        $this->fileSystem = $fileSystem;
        $this->imageDir = $imageDir;
        $this->imageResizeDir = $imageResizeDir;
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
        $resizeName =  substr(strrchr($imageSrc, "/"), 1);
        $resizeDir = $this->imageResizeDir . $width . "x" . $height . "/" . str_replace($resizeName, "", $imageSrc);
        $resizeSrc = $resizeDir . $resizeName;

        if(!$this->fileSystem->exists($resizeSrc)) {

            if(!$this->fileSystem->exists($resizeDir)) {
                $this->fileSystem->mkdir($resizeDir);
            }

            $manager = new ImageManager(['driver' => 'imagick']);

            $manager
                ->make($this->imageDir . $imageSrc)
                ->resize($width, $height)
                ->save($resizeSrc)
            ;
        }

        echo "/" . $resizeSrc;
    }
}