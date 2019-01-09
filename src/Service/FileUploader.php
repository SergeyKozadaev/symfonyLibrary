<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    public function upload(UploadedFile $file, string $targetDirectory)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $randomDir = $this->generateRandomString().'/';

        try {
            $file->move($targetDirectory.$randomDir, $fileName);
        } catch (FileException $e) {
            return null;
        }

        return $randomDir.$fileName;
    }

    private function generateRandomString($length = 3)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charsLength = strlen($chars);
        $randomString = '';

        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $chars[rand(0, $charsLength - 1)];
        }

        return $randomString;
    }
}
