<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{

    public function upload(UploadedFile $file, $targetDirectory)
    {
        $fileName = md5(uniqid()).'.'.$file->guessExtension();
        $randomDir = $this->generateRandomDir() . "/";

        try {
            $file->move($targetDirectory . $randomDir, $fileName);
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }

        return $randomDir . $fileName;
    }

    public function generateRandomDir($length = 3)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charsLength = strlen($chars);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $chars[rand(0, $charsLength - 1)];
        }

        return $randomString;
    }
}