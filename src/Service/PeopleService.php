<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\People;

class PeopleService
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create(People $person){
        $this->hasValidId($person); 
        $this->hasValidDOB($person); 
        $this->hasMatchingDOBAndId($person); 
        $this->isAlphanumeric($person->getName());
        $this->isAlphanumeric($person->getName());
        $this->isDuplicateId($person);
        $this->em->persist($person);
        $this->em->flush();
    }

    public function findAll(){
        $repository = $this->em->getRepository(People::class);
        return $repository->findAll();
    }

    private function isDuplicateId(People $person){
        $repository = $this->em->getRepository(People::class);
        $person = $repository->findOneBy(['id_number' => $person->getIdNumber()]);
        if($person){
            throw new \InvalidArgumentException('ID Already taken!');
        }
    }

    private function hasValidId(People $person)
    {
        if(!strlen($person->getIdNumber()) === 13){
            throw new \InvalidArgumentException('Invalid Id!');
        }
    }

    private function hasValidDOB(People $person)
    {
        $validationDate = \DateTime::createFromFormat('d/m/Y', $person->getDob());
        if(!($validationDate && $validationDate->format('d/m/Y') == $person->getDob())){
            throw new \InvalidArgumentException('Invalid Dob Format!');
        }
    }

    private function hasMatchingDOBAndId(People $person)
    {
        if(! 
            ((substr($person->getIdNumber(), 0, 2) === substr($person->getDob(), 8, 2))
            && (substr($person->getIdNumber(), 2, 2) === substr($person->getDob(), 3, 2))
            && (substr($person->getIdNumber(), 4, 2) === substr($person->getDob(), 0, 2)))){
                throw new \InvalidArgumentException('Dob not Matching!');
            }
    }

    private function isAlphanumeric(string $text){
        if(! (preg_match("/^[a-zA-Z'-]+$/", $text))){
            throw new \InvalidArgumentException('Check name or Surname!');
        }
    }


}