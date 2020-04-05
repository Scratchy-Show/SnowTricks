<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDirectory;

    public function __construct($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file, $destination)
    {
        // Redéfini le nom du fichier
        $newFilename =  uniqid() . '.' . $file->guessExtension();

        // Déplace le fichier dans le dossier cible et le renomme
        $file->move($this->getTargetDirectory($destination), $newFilename);

        // Retourne le nouveau nom du fichier
        return $newFilename;
    }

    public function getTargetDirectory($destination)
    {
        // Défini le dossier cible
        $this->targetDirectory = $destination;

        return $this->targetDirectory;
    }
}