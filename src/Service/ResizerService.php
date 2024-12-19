<?php

namespace App\Service;

use Spatie\Image\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ResizerService {

    /**
     * Redimensionner une image et retourner son chemin relatif.
     *
     * @param string $image Le nom du fichier image
     * @param string $imagesDirectory Le répertoire contenant l'image source
     * @param string $resizedDirectory Le répertoire où enregistrer l'image redimensionnée
     * 
     * @return string|false Le chemin relatif de l'image redimensionnée, ou false en cas d'erreur
     */
    public function resize($image, $imagesDirectory, $resizedDirectory) {
        $sourcePath = $imagesDirectory . DIRECTORY_SEPARATOR . $image;
        $relativePath = $resizedDirectory . DIRECTORY_SEPARATOR . pathinfo($image, PATHINFO_FILENAME) . '.webp';
        $destinationPath = getcwd() . DIRECTORY_SEPARATOR . $relativePath;
    
        $optimizerChain = OptimizerChainFactory::create();
    
        if (@getimagesize($sourcePath)) {
            try {
                // Optimiser et enregistrer l'image
                Image::load($sourcePath)
                    ->format('webp')
                    ->quality(80)
                    ->optimize($optimizerChain)
                    ->save($destinationPath);
    
                // Retourner le chemin relatif
                return $relativePath;
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
    
    
}