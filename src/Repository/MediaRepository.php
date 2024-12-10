<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

    /**
     * @extends ServiceEntityRepository<Media>
     *
     * @method Media|null find($id, $lockMode = null, $lockVersion = null)
     * @method Media|null findOneBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null)
     * @method Media[]    findAll()
     * @method Media[]    findBy(array<string, mixed> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
     */
    class MediaRepository extends ServiceEntityRepository
    {
        public function __construct(ManagerRegistry $registry)
        {
            parent::__construct($registry, Media::class);
        }


    /**
     * Récupère tous les médias non restreints
     *
     * @return Media[] Retourne un tableau de médias
     */
    public function findAllMediasNotRestricted(): array
    {
        return $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les médias non restreints d'un album spécifique
     *
     * @param Album $album L'album pour lequel récupérer les médias
     * 
     * @return Media[] Retourne un tableau de médias
     */
    public function findAllMediasNotRestrictedByAlbum(Album $album): array
    {
        return $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->andWhere('media.album = :album')
            ->setParameter('album', $album)
            ->getQuery()
            ->getResult();
    }


    /**
     * Récupère les médias non restreints avec pagination
     * 
     * @param int $limit Le nombre maximal d'éléments à retourner
     * @param int $offset L'offset pour la pagination
     * 
     * @return Media[] Retourne un tableau de médias
     */
    public function findAllMediasNotRestrictedWithPagination(int $limit, int $offset): array
    {
        return $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->setFirstResult($offset)   // Applique l'offset pour la pagination
            ->setMaxResults($limit)     // Applique la limite pour la pagination
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les médias non restreints d'un album spécifique avec pagination
     * 
     * @param Album $album L'album pour lequel récupérer les médias
     * @param int $limit Le nombre maximal d'éléments à retourner
     * @param int $offset L'offset pour la pagination
     * 
     * @return Media[] Retourne un tableau de médias
     */
    public function findAllMediasNotRestrictedByAlbumWithPagination(Album $album, int $limit, int $offset): array
    {
        return $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->andWhere('media.album = :album')
            ->setParameter('album', $album)
            ->setFirstResult($offset)   // Applique l'offset pour la pagination
            ->setMaxResults($limit)     // Applique la limite pour la pagination
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de médias pour un album spécifique
     * 
     * @param Album $album L'album pour lequel compter les médias
     * 
     * @return int Retourne le nombre de médias dans cet album
     */
    public function countMediasByAlbum(Album $album): int
    {
        return (int) $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->andWhere('media.album = :album')
            ->setParameter('album', $album)
            ->select('COUNT(media.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre total de médias
     * 
     * @return int Retourne le nombre total de médias
     */
    public function countAllMedias(): int
    {
        return (int) $this->createQueryBuilder('media')
            ->join('media.user', 'user')
            ->where('user.restricted = false')
            ->select('COUNT(media.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
