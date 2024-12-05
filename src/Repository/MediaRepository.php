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
     * @return Media[] Returns an array of Media objects
     * @phpstan-return array<Media>
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
     * @param Album $album
     * @return Media[] Returns an array of Media objects
     * @phpstan-return array<Media>
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
}

//    /**
//     * @return Media[] Returns an array of Media objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Media
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
