<?php 

namespace App\Controller;

use Spatie\ImageOptimizer\OptimizerChainFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Spatie\Image\Image;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ResizerController extends AbstractController {

    #[IsGranted("ROLE_ADMIN")]
    #[Route("/optimize", name: "optimize_images")]
    public function optimizeImages()
    {
        $imagesDirectory = $this->getParameter('kernel.project_dir').'/public/uploads/nature/';
        $resizedDirectory = $this->getParameter('kernel.project_dir').'/public/uploadsResized/nature/';
        
        $filesystem = new Filesystem();
        if (!$filesystem->exists($resizedDirectory)) {
            $filesystem->mkdir($resizedDirectory, 0755);
        }
        
        $images = scandir($imagesDirectory);
        $optimizerChain = OptimizerChainFactory::create();

        foreach ($images as $image) {
            // Ignore non-image files and hidden files
            if ($image === "." || $image === "..") {
                continue;
            }

            $sourcePath = $imagesDirectory . $image;
            $destinationPath = $resizedDirectory . pathinfo($image, PATHINFO_FILENAME) . '.webp';

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