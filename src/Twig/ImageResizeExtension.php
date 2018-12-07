<?php

namespace App\Twig;

use Intervention\Image\ImageManager;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageResizeExtension extends AbstractExtension
{
    const IMAGE_DIR = 'upload/images/';
    const RESIZE_DIR = 'upload/images/resize/';

    private $fileSystem;

    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
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

    public function imageResize(string $imgSrc, int $width, int $height)
    {
        $resizeName =  substr(strrchr($imgSrc, "/"), 1);
        $resizeDir = self::RESIZE_DIR . $width . "x" . $height . "/" . str_replace($resizeName, "", $imgSrc);
        $resizeSrc = $resizeDir . $resizeName;

        if(!$this->fileSystem->exists($resizeSrc)) {

            if(!$this->fileSystem->exists($resizeDir)) {
                $this->fileSystem->mkdir($resizeDir);
            }

            $manager = new ImageManager(['driver' => 'imagick']);

            $manager
                ->make(self::IMAGE_DIR . $imgSrc)
                ->resize($width, $height)
                ->save($resizeSrc);
        }

        echo $resizeSrc;
    }

}