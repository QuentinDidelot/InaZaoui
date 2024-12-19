<?php

namespace App\Service;

use Spatie\Image\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ResizerService {

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
                dd($e->getMessage());
                return false;
            }
        } else {
            dd($sourcePath);
            return false;
        }
    }
    
    
}