<?php

namespace ContribuxBundle\Repository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use Doctrine\ORM\EntityRepository;

/**
 * ProjetRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjetRepository extends \Doctrine\ORM\EntityRepository
{

    public function getAllProjets($page, $maxPage) {

        if ($page < 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas');
        }

        $qb = $this->createQueryBuilder('p');
        $query = $qb->getQuery();

        $firstResult = ($page - 1) * $maxPage;
        $query->setFirstResult($firstResult)->setMaxResults($maxPage); //renvoie suelement les résultats souhaités
        $paginator = new Paginator($query);

       return $paginator;


    }


    public function getMyProjets($page, $maxPage, $user) {

        if ($page < 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas');
        }

        $qb = $this->createQueryBuilder('p');
        $qb->where('p.user = :user');
        $qb->setParameter('user',$user);


        $query = $qb->getQuery();

        $firstResult = ($page - 1) * $maxPage;
        $query->setFirstResult($firstResult)->setMaxResults($maxPage);
        $paginator = new Paginator($query);

        return $paginator;


    }
}
