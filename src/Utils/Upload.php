<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Upload
{

    public function saveFile(UploadedFile $file, string $name,string $dir)
    {
        /**
         * permet d'accéder à l'autocomplétion quand le type d'une variable n'est pas connu
         */
        $newFilename = $name . '-' . uniqid() . '.' . $file->guessExtension();
        try {
            $file->move($dir, $newFilename);
        } catch (FileException $e) {
            dump($e->getMessage());
        }

        return $newFilename;

    }

}