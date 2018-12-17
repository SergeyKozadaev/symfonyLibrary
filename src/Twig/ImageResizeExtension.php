<?php

namespace App\Twig;

use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ImageResizeExtension extends AbstractExtension
{
    private $fileSystem;
    private $params;

    public function __construct(Filesystem $fileSystem, ContainerBagInterface $bag)
    {
        $this->fileSystem = $fileSystem;
        $this->params = $bag;
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
        $imagePath = $this->params->get('images_directory') . $imageSrc;
        if(!$this->fileSystem->exists($imagePath)) {
            $imagePath = $this->params->get('stub_image');
        }

        echo "
                    <div class=\"img-container\" style=\"width: " . $width . "px; height: " . $height . "px\">
                        <img src='". "/" . $imagePath . "'>
                    </div>
                 "
        ;
    }
}