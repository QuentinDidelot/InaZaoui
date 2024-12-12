<?php

namespace App\Controller;

use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Spatie\Image\Image;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ResizerController extends AbstractController
{
    #[IsGranted("ROLE_ADMIN")]
    #[Route("/optimize", name: "optimize_images")]
    public function optimizeImages(): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        
        if (!is_string($projectDir)) {
            throw new \RuntimeException('The project directory parameter is not a valid string.');
        }

        $imagesDirectory = $projectDir . '/public/uploads/nature/';
        $resizedDirectory = $projectDir . '/public/uploadsResized/nature/';

        $filesystem = new Filesystem();
        if (!$filesystem->exists($resizedDirectory)) {
            $filesystem->mkdir($resizedDirectory, 0755);
        }

        // Vérification que scandir ne retourne pas false
        $images = scandir($imagesDirectory);
        if ($images === false) {
            $this->addFlash('error', "Failed to read the images directory.");
            return $this->render('resizer/optimization_result.html.twig', [
                'message' => 'Error reading images directory.',
            ]);
        }

        $optimizerChain = OptimizerChainFactory::create();

        // Filtrer les images valides (ignore les fichiers cachés et non-images)
        foreach ($images as $image) {
            // Ignore non-image files and hidden files
            if ($image === "." || $image === "..") {
                continue;
            }

            $sourcePath = $imagesDirectory . DIRECTORY_SEPARATOR . $image;
            $destinationPath = $resizedDirectory . DIRECTORY_SEPARATOR . pathinfo($image, PATHINFO_FILENAME) . '.webp';

            // Check if the file is an image
            if (@getimagesize($sourcePath)) {
                try {
                    // Optimize and save the image
                    Image::load($sourcePath)
                        ->format('webp')
                        ->quality(80)
                        ->optimize($optimizerChain)
                        ->save($destinationPath);

                    // Log success
                    $this->addFlash('success', "Image optimized: $image");
                } catch (\Exception $e) {
                    // Log error
                    $this->addFlash('error', "Failed to optimize $image: " . $e->getMessage());
                }
            } else {
                $this->addFlash('warning', "Skipped non-image file: $image");
            }
        }

        return $this->render('resizer/optimization_result.html.twig', [
            'message' => 'Images have been processed.',
        ]);
    }
}
